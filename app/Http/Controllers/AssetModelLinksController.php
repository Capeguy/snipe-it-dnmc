<?php

namespace App\Http\Controllers;

use App\Helpers\Helper;
use App\Http\Requests\ImageUploadRequest;
use App\Models\Actionlog;
use App\Models\Asset;
use App\Models\AssetLink;
use App\Models\AssetModel;
use App\Models\CustomField;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Validator;
use Redirect;
use Request;
use Storage;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * This class controls all actions related to asset models for
 * the Snipe-IT Asset Management application.
 *
 * @version    v1.0
 * @author [A. Gianotto] [<snipe@snipe.net>]
 */
class AssetModelLinksController extends Controller
{
    /**
     * Returns a view that invokes the ajax tables which actually contains
     * the content for the accessories listing, which is generated in getDatatable.
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     * @since [v1.0]
     * @return View
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function index()
    {
        $this->authorize('index', AssetModel::class);

        return view('model_links/index');
    }

    /**
     * Returns a view containing the asset model creation form.
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     * @since [v1.0]
     * @return View
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function create()
    {
        $this->authorize('create', AssetLink::class);

        return view('model_links/edit')
            ->with('item', new AssetLink);
    }

    /**
     * Validate and process the new Asset Model data.
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     * @since [v1.0]
     * @param ImageUploadRequest $request
     * @return Redirect
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function store(ImageUploadRequest $request)
    {
        $this->authorize('create', AssetLink::class);
        // Create a new asset model
        $model = new AssetLink;

        // Save the model data
        $model->model_id = $request->input('model_id');
        $model->related_model_id = $request->input('related_model_id');

        // Was it created?
        if ($model->save()) {
            if ($this->shouldAddDefaultValues($request->input())) {
                if (!$this->assignCustomFieldsDefaultValues($model, $request->input('default_values'))){
                    return redirect()->back()->withInput()->with('error', trans('admin/custom_fields/message.fieldset_default_value.error'));
                }
            }

            return redirect()->route('model_links.index')->with('success', trans('admin/model_links/message.create.success'));
        }

        return redirect()->back()->withInput()->withErrors($model->getErrors());
    }

    /**
     * Returns a view containing the asset model link edit form.
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     * @since [v1.0]
     * @param int $assetModelLinkId
     * @return View
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function edit($assetModelLinkId = null)
    {
        $this->authorize('update', AssetLink::class);
        if ($item = AssetLink::find($assetModelLinkId)) {
            $category_type = 'asset_link';
            $view = View::make('model_links/edit', compact('item', 'category_type'));

            return $view;
        }

        return redirect()->route('model_links.index')->with('error', trans('admin/models/message.does_not_exist'));
    }


    /**
     * Validates and processes form data from the edit
     * Asset Model form based on the model ID passed.
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     * @since [v1.0]
     * @param ImageUploadRequest $request
     * @param int $assetModelLinkId
     * @return Redirect
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function update(ImageUploadRequest $request, $assetModelLinkId = null)
    {
        $this->authorize('update', AssetModel::class);
        // Check if the model exists
        if (is_null($model = AssetLink::find($assetModelLinkId))) {
            // Redirect to the models management page
            return redirect()->route('model_links.index')->with('error', trans('admin/model_links/message.does_not_exist'));
        }

        $model = $request->handleImages($model);

        $model->model_id = $request->input('model_id');
        $model->related_model_id = $request->input('related_model_id');

        if ($model->save()) {
            return redirect()->route('model_links.index')->with('success', trans('admin/model_links/message.update.success'));
        }

        return redirect()->back()->withInput()->withErrors($model->getErrors());
    }

    /**
     * Validate and delete the given Asset Model. An Asset Model
     * cannot be deleted if there are associated assets.
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     * @since [v1.0]
     * @param int $assetModelLinkId
     * @return Redirect
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function destroy($assetModelLinkId)
    {
        $this->authorize('delete', AssetLink::class);
        // Check if the model exists
        if (is_null($model = AssetLink::find($assetModelLinkId))) {
            return redirect()->route('model_links.index')->with('error', trans('admin/model_links/message.does_not_exist'));
        }

        // Delete the model
        $model->delete();

        // Redirect to the models management page
        return redirect()->route('model_links.index')->with('success', trans('admin/model_links/message.delete.success'));
    }

    /**
     * Restore a given Asset Model (mark as un-deleted)
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     * @since [v1.0]
     * @param int $id
     * @return Redirect
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function getRestore($id)
    {
        $this->authorize('create', AssetModel::class);

        if ($model = AssetModel::withTrashed()->find($id)) {

            if ($model->deleted_at == '') {
                return redirect()->back()->with('error', trans('general.not_deleted', ['item_type' => trans('general.asset_model')]));
            }

            if ($model->restore()) {
                $logaction = new Actionlog();
                $logaction->item_type = User::class;
                $logaction->item_id = $model->id;
                $logaction->created_at = date('Y-m-d H:i:s');
                $logaction->user_id = Auth::user()->id;
                $logaction->logaction('restore');


                // Redirect them to the deleted page if there are more, otherwise the section index
                $deleted_models = AssetModel::onlyTrashed()->count();
                if ($deleted_models > 0) {
                    return redirect()->back()->with('success', trans('admin/models/message.restore.success'));
                }
                return redirect()->route('models.index')->with('success', trans('admin/models/message.restore.success'));
            }

            // Check validation
            return redirect()->back()->with('error', trans('general.could_not_restore', ['item_type' => trans('general.asset_model'), 'error' => $model->getErrors()->first()]));
        }

        return redirect()->back()->with('error', trans('admin/models/message.does_not_exist'));

    }


    /**
     * Get the model information to present to the model view page
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     * @since [v1.0]
     * @param int $assetModelLinkId
     * @return View
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function show($assetModelLinkId = null)
    {
        $this->authorize('view', AssetModel::class);
        $model = AssetLink::withTrashed()->withCount('assets')->find($assetModelLinkId);

        if (isset($model->id)) {
            return view('model_links/view', compact('model'));
        }

        return redirect()->route('model_links.index')->with('error', trans('admin/model_links/message.does_not_exist'));
    }

    /**
     * Get the clone page to clone a model
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     * @since [v1.0]
     * @param int $modelId
     * @return View
     */
    public function getClone($modelId = null)
    {
        $this->authorize('create', AssetModel::class);
        // Check if the model exists
        if (is_null($model_to_clone = AssetModel::find($modelId))) {
            return redirect()->route('models.index')->with('error', trans('admin/models/message.does_not_exist'));
        }

        $model = clone $model_to_clone;
        $model->id = null;

        // Show the page
        return view('models/edit')
            ->with('depreciation_list', Helper::depreciationList())
            ->with('item', $model)
            ->with('model_id', $model_to_clone->id)
            ->with('clone_model', $model_to_clone);
    }


    /**
     * Get the custom fields form
     *
     * @author [B. Wetherington] [<uberbrady@gmail.com>]
     * @since [v2.0]
     * @param int $modelId
     * @return View
     */
    public function getCustomFields($modelId)
    {
        return view('models.custom_fields_form')->with('model', AssetModel::find($modelId));
    }



    /**
     * Returns a view that allows the user to bulk edit model attrbutes
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     * @since [v1.7]
     * @return \Illuminate\Contracts\View\View
     */
    public function postBulkEdit(Request $request)
    {
        $models_raw_array = $request->input('ids');

        // Make sure some IDs have been selected
        if ((is_array($models_raw_array)) && (count($models_raw_array) > 0)) {
            $models = AssetModel::whereIn('id', $models_raw_array)->withCount('assets as assets_count')->orderBy('assets_count', 'ASC')->get();

            // If deleting....
            if ($request->input('bulk_actions') == 'delete') {
                $valid_count = 0;
                foreach ($models as $model) {
                    if ($model->assets_count == 0) {
                        $valid_count++;
                    }
                }

                return view('models/bulk-delete', compact('models'))->with('valid_count', $valid_count);

            // Otherwise display the bulk edit screen
            } else {
                $nochange = ['NC' => 'No Change'];
                $fieldset_list = $nochange + Helper::customFieldsetList();
                $depreciation_list = $nochange + Helper::depreciationList();

                return view('models/bulk-edit', compact('models'))
                    ->with('fieldset_list', $fieldset_list)
                    ->with('depreciation_list', $depreciation_list);
            }
        }

        return redirect()->route('models.index')
            ->with('error', 'You must select at least one model to edit.');
    }



    /**
     * Returns a view that allows the user to bulk edit model attrbutes
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     * @since [v1.7]
     * @return \Illuminate\Contracts\View\View
     */
    public function postBulkEditSave(Request $request)
    {
        $models_raw_array = $request->input('ids');
        $update_array = [];


        if (($request->filled('manufacturer_id') && ($request->input('manufacturer_id') != 'NC'))) {
            $update_array['manufacturer_id'] = $request->input('manufacturer_id');
        }
        if (($request->filled('category_id') && ($request->input('category_id') != 'NC'))) {
            $update_array['category_id'] = $request->input('category_id');
        }
        if ($request->input('fieldset_id') != 'NC') {
            $update_array['fieldset_id'] = $request->input('fieldset_id');
        }
        if ($request->input('depreciation_id') != 'NC') {
            $update_array['depreciation_id'] = $request->input('depreciation_id');
        }

        
        if (count($update_array) > 0) {
            AssetModel::whereIn('id', $models_raw_array)->update($update_array);

            return redirect()->route('models.index')
                ->with('success', trans('admin/models/message.bulkedit.success'));
        }

        return redirect()->route('models.index')
            ->with('warning', trans('admin/models/message.bulkedit.error'));
    }

    /**
     * Validate and delete the given Asset Models. An Asset Model
     * cannot be deleted if there are associated assets.
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     * @since [v1.0]
     * @param int $modelId
     * @return Redirect
     */
    public function postBulkDelete(Request $request)
    {
        $models_raw_array = $request->input('ids');

        if ((is_array($models_raw_array)) && (count($models_raw_array) > 0)) {
            $models = AssetModel::whereIn('id', $models_raw_array)->withCount('assets as assets_count')->get();

            $del_error_count = 0;
            $del_count = 0;

            foreach ($models as $model) {

                if ($model->assets_count > 0) {
                    $del_error_count++;
                } else {
                    $model->delete();
                    $del_count++;
                }
            }


            if ($del_error_count == 0) {
                return redirect()->route('models.index')
                    ->with('success', trans('admin/models/message.bulkdelete.success', ['success_count'=> $del_count]));
            }

            return redirect()->route('models.index')
                ->with('warning', trans('admin/models/message.bulkdelete.success_partial', ['fail_count'=>$del_error_count, 'success_count'=> $del_count]));
        }

        return redirect()->route('models.index')
            ->with('error', trans('admin/models/message.bulkdelete.error'));
    }

    /**
     * Returns true if a fieldset is set, 'add default values' is ticked and if
     * any default values were entered into the form.
     *
     * @param  array  $input
     * @return bool
     */
    private function shouldAddDefaultValues(array $input)
    {
        return ! empty($input['add_default_values'])
            && ! empty($input['default_values'])
            && ! empty($input['fieldset_id']);
    }

    /**
     * Adds default values to a model (as long as they are truthy)
     *
     * @param  AssetModel $model
     * @param  array      $defaultValues
     * @return void
     */
    private function assignCustomFieldsDefaultValues(AssetModel $model, array $defaultValues): bool
    {
        $data = array();
        foreach ($defaultValues as $customFieldId => $defaultValue) {
            $customField = CustomField::find($customFieldId);

            $data[$customField->db_column] = $defaultValue;
        }

        $fieldsets = $model->fieldset->validation_rules();
        $rules = array();

        foreach ($fieldsets as $fieldset => $validation){
            // If the field is marked as required, eliminate the rule so it doesn't interfere with the default values
            // (we are at model level, the rule still applies when creating a new asset using this model)
            $index = array_search('required', $validation);
            if ($index !== false){
                $validation[$index] = 'nullable';
            }
            $rules[$fieldset] = $validation;
        }

        $validator = Validator::make($data, $rules);

        if($validator->fails()){
            return false;
        }

        foreach ($defaultValues as $customFieldId => $defaultValue) {
            if(is_array($defaultValue)){
                $model->defaultValues()->attach($customFieldId, ['default_value' => implode(', ', $defaultValue)]);
            }elseif ($defaultValue) {
                $model->defaultValues()->attach($customFieldId, ['default_value' => $defaultValue]);
            }
        }
        return true;
    }

    /**
     * Removes all default values
     *
     * @return void
     */
    private function removeCustomFieldsDefaultValues(AssetModel $model)
    {
        $model->defaultValues()->detach();
    }
}
