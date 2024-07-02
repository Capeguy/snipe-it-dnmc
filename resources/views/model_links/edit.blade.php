@extends('layouts/edit-form', [
    'createText' => trans('admin/model_links/table.create'),
    'updateText' => trans('admin/model_links/table.update'),
    'topSubmit' => true,
    'helpPosition' => 'right',
    'helpText' => 'Link an Asset Model to another Asset Model',
    'formAction' => (isset($item->id)) ? route('model_links.update', ['model_link' => $item->id]) : route('model_links.store'),
])

{{-- Page content --}}
@section('inputFields')

@include ('partials.forms.edit.asset-model-select', ['translated_name' => 'Asset Model',  'fieldname' => 'model_id', 'required' => true])
@include ('partials.forms.edit.asset-model-select', ['translated_name' => 'Related Asset Model', 'fieldname' => 'related_model_id', 'required' => true])

<!-- EOL -->
@stop