<!-- Asset Model -->
<div id="{{ $fieldname }}" class="form-group{{ $errors->has($fieldname) ? ' has-error' : '' }}">

    {{ Form::label($fieldname, $translated_name, array('class' => 'col-md-3 control-label')) }}

    <div class="col-md-7{{  ((isset($required)) && ($required=='true')) ? ' required' : '' }}">
        <select class="js-data-ajax" data-endpoint="models" data-placeholder="Select an Asset Model" name="{{ $fieldname }}" style="width: 100%" id="{{$fieldname}}_select_id" aria-label="{{ $fieldname }}" {!!  ((isset($item)) && (Helper::checkIfRequired($item, $fieldname))) ? ' data-validation="required" required' : '' !!}{{ (isset($multiple) && ($multiple=='true')) ? " multiple='multiple'" : '' }}>
            @if ($model_id = old($fieldname,  (isset($item)) ? $item->{$fieldname} : ''))
                <option value="{{ $model_id }}" selected="selected" role="option" aria-selected="true"  role="option">
                    {{ (\App\Models\AssetModel::find($model_id)) ? \App\Models\AssetModel::find($model_id)->name : '' }}
                </option>
            @else
                <option value=""  role="option">Select an Asset Model</option>
            @endif

        </select>
    </div>

    <div class="col-md-1 col-sm-1 text-left">
        @can('create', \App\Models\AssetModel::class)
            @if ((!isset($hide_new)) || ($hide_new!='true'))
                <a href='{{ route('modal.show', 'model') }}' data-toggle="modal"  data-target="#createModal" data-select='{{$fieldname}}_select_id' class="btn btn-sm btn-primary">{{ trans('button.new') }}</a>
            @endif
        @endcan
    </div>


    {!! $errors->first($fieldname, '<div class="col-md-8 col-md-offset-3"><span class="alert-msg" aria-hidden="true"><i class="fas fa-times" aria-hidden="true"></i> :message</span></div>') !!}
</div>
