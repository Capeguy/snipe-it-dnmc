<?php

namespace App\Presenters;

use App\Helpers\Helper;

/**
 * Class AssetModelPresenter
 */
class AssetModelLinkPresenter extends Presenter
{
    public static function dataTableLayout()
    {
        $layout = [
            [
                'field' => 'checkbox',
                'checkbox' => true,
            ],
            [
                'field' => 'id',
                'searchable' => false,
                'sortable' => true,
                'switchable' => true,
                'title' => trans('general.id'),
                'visible' => false,
            ], [
                'field' => 'model_id',
                'searchable' => true,
                'sortable' => true,
                'switchable' => false,
                'visible' => false,
                'title' => 'Source Model',
            ], [
                'field' => 'model_name',
                'searchable' => true,
                'sortable' => true,
                'switchable' => false,
                'visible' => true,
                'title' => 'Source Model',
                'formatter' => 'model_linksLinkFormatter',
            ], [
                'field' => 'related_model_id',
                'searchable' => true,
                'sortable' => true,
                'switchable' => false,
                'visible' => false,
                'title' => 'Target Model',
            ], [
                'field' => 'related_model_name',
                'searchable' => true,
                'sortable' => true,
                'switchable' => false,
                'visible' => true,
                'title' => 'Target Model',
                'formatter' => 'related_model_linksLinkFormatter',
            ], 
            [
                'field' => 'created_at',
                'searchable' => true,
                'sortable' => true,
                'visible' => false,
                'title' => trans('general.created_at'),
                'formatter' => 'dateDisplayFormatter',
            ],
            [
                'field' => 'updated_at',
                'searchable' => true,
                'sortable' => true,
                'visible' => false,
                'title' => trans('general.updated_at'),
                'formatter' => 'dateDisplayFormatter',
            ],
        ];

        $layout[] = [
            'field' => 'actions',
            'searchable' => false,
            'sortable' => false,
            'switchable' => false,
            'title' => trans('table.actions'),
            'formatter' => 'model_linksActionsFormatter',
        ];

        return json_encode($layout);
    }

    /**
     * Formatted note for this model
     * @return string
     */
    public function note()
    {
        if ($this->model->note) {
            return Helper::parseEscapedMarkedown($this->model->note);
        }
    }

    public function eolText()
    {
        if ($this->eol) {
            return $this->eol.' '.trans('general.months');
        }

        return '';
    }

    /**
     * Pretty name for this model
     * @return string
     */
    public function modelName()
    {
        $name = '';
        if ($this->model->manufacturer) {
            $name .= $this->model->manufacturer->name.' ';
        }
        $name .= $this->name;

        if ($this->model_number) {
            $name .= ' (#'.$this->model_number.')';
        }

        return $name;
    }

    /**
     * Standard url for use to view page.
     * @return string
     */
    public function nameUrl()
    {
        return  (string) link_to_route('models.show', $this->name, $this->id);
    }

    /**
     * Generate img tag to this models image.
     * @return string
     */
    public function imageUrl()
    {
        if (! empty($this->image)) {
            return '<img src="'.config('app.url').'/uploads/models/'.$this->image.'" alt="'.$this->name.'" height="50" width="50">';
        }

        return '';
    }

    /**
     * Generate img tag to this models image.
     * @return string
     */
    public function imageSrc()
    {
        if (! empty($this->image)) {
            return config('app.url').'/uploads/models/'.$this->image;
        }

        return '';
    }

    /**
     * Url to view this item.
     * @return string
     */
    public function viewUrl()
    {
        return route('models.show', $this->id);
    }
}
