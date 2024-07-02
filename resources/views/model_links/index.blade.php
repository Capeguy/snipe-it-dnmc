@extends('layouts/default')

{{-- Page title --}}
@section('title')

  @if (Request::get('status')=='deleted')
    {{ trans('admin/model_links/general.view_deleted') }}
    {{ trans('admin/model_links/table.title') }}
    @else
    {{ trans('admin/model_links/general.view_model_links') }}
  @endif
@parent
@stop

{{-- Page title --}}
@section('header_right')
  @can('create', \App\Models\AssetModel::class)
    <a href="{{ route('model_links.create') }}" class="btn btn-primary pull-right"> {{ trans('general.create') }} Model Link</a>
  @endcan

  <!-- @if (Request::get('status')=='deleted')
    <a class="btn btn-default pull-right" href="{{ route('models.index') }}" style="margin-right: 5px;">{{ trans('admin/model_links/general.view_model_links') }}</a>
  @else
    <a class="btn btn-default pull-right" href="{{ route('models.index', ['status' => 'deleted']) }}" style="margin-right: 5px;">{{ trans('admin/model_links/general.view_deleted') }}</a>
  @endif -->

@stop


{{-- Page content --}}
@section('content')


<div class="row">
  <div class="col-md-12">
    <div class="box box-default">
      <div class="box-body">

{{--        @include('partials.asset-links-bulk-actions')--}}
              <div class="table-responsive">
                <table
                        data-columns="{{ \App\Presenters\AssetModelLinkPresenter::dataTableLayout() }}"
                        data-cookie-id-table="assetModelsTable"
                        data-pagination="true"
                        data-id-table="assetModelsTable"
                        data-search="true"
                        data-show-footer="true"
                        data-side-pagination="server"
                        data-show-columns="true"
                        data-toolbar="#modelsBulkEditToolbar"
                        data-bulk-button-id="#bulkModelsEditButton"
                        data-bulk-form-id="#modelsBulkForm"
                        data-show-export="true"
                        data-show-refresh="true"
                        data-sort-order="asc"
                        id="assetModelsTable"
                        class="table table-striped snipe-table"
                        data-url="{{ route('api.model_links.index', ['status' => request('status')]) }}"
                        data-export-options='{
              "fileName": "export-models-{{ date('Y-m-d') }}",
              "ignoreColumn": ["actions","image","change","checkbox","checkincheckout","icon"]
              }'>
              </table>

          </div>
        </div>
        </div>
        {{ Form::close() }}
      </div><!-- /.box-body -->
    </div><!-- /.box -->
  </div>
</div>

@stop

@section('moar_scripts')
@include ('partials.bootstrap-table', ['exportFile' => 'models-export', 'search' => true])

@stop
