<?php

namespace App\Http\Transformers;

use App\Helpers\Helper;
use App\Models\AssetLink;
use App\Models\AssetModel;
use Illuminate\Support\Facades\Gate;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Storage;

class AssetModelLinksTransformer
{
    public function transformAssetModelLinks(Collection $assetmodellinks, $total)
    {
        $array = [];
        foreach ($assetmodellinks as $assetmodellink) {
            $array[] = self::transformAssetModelLink($assetmodellink);
        }

        return (new DatatablesTransformer)->transformDatatables($array, $total);
    }

    public function transformAssetModelLink(AssetLink $assetmodellink)
    {

        $default_field_values = array();

        // Reach into the custom fields and models_custom_fields pivot table to find the default values for this model
        if ($assetmodellink->fieldset) {
            foreach($assetmodellink->fieldset->fields AS $field) {
                $default_field_values[] = [
                    'name' => e($field->name),
                    'db_column_name' => e($field->db_column_name()),
                    'default_value' => ($field->defaultValue($assetmodellink->id)) ? e($field->defaultValue($assetmodellink->id)) : null,
                    'format' =>  e($field->format),
                    'required' => ($field->pivot->required == '1') ? true : false,
                ];
            }
        }

        $array = [
            'id' => (int) $assetmodellink->id,
            'model_id' => $assetmodellink->model->name,
            'related_model_id' => $assetmodellink->related_model->name,
            // 'created_at' => Helper::getFormattedDateObject($assetmodellink->created_at, 'datetime'),
            // 'updated_at' => Helper::getFormattedDateObject($assetmodellink->updated_at, 'datetime'),
            // 'deleted_at' => Helper::getFormattedDateObject($assetmodellink->deleted_at, 'datetime'),
        ];

        $permissions_array['available_actions'] = [
            'update' => (Gate::allows('update', AssetModel::class) && ($assetmodellink->deleted_at == '')),
            'delete' => $assetmodellink->isDeletable(),
            'clone' => (Gate::allows('create', AssetModel::class) && ($assetmodellink->deleted_at == '')),
            'restore' => (Gate::allows('create', AssetModel::class) && ($assetmodellink->deleted_at != '')),
        ];

        $array += $permissions_array;

        return $array;
    }

    public function transformAssetModelsDatatable($assetmodellinks)
    {
        return (new DatatablesTransformer)->transformDatatables($assetmodellinks);
    }
}
