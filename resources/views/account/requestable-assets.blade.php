@extends('layouts/default')

@section('title0')
  {{ trans('admin/hardware/general.requestable') }}
  {{ trans('general.assets') }}
@stop

{{-- Page title --}}
@section('title')
    @yield('title0')  @parent
@stop

{{-- Page content --}}
@section('content')

<div class="row">
    <div class="col-md-12">


        @if (($assets->count() < 1) && ($models->count() < 1))

            <div class="col-md-12">
                <div class="alert alert-info fade in">
                    <i class="fas fa-info-circle faa-pulse animated"></i>
                    <strong>{{ trans('general.notification_info') }}: </strong>
                    {{ trans('general.no_requestable') }}
                </div>
            </div>

        @else
        <div class="nav-tabs-custom">
            <ul class="nav nav-tabs">
                @if ($assets->count() > 0)
                <li class="active">
                    <a href="#assets" data-toggle="tab" title="{{ trans('general.assets') }}">{{ trans('general.assets') }}
                        <badge class="badge badge-secondary"> {{ $assets->count()}}</badge>
                    </a>               
                </li>
                @endif
                @if ($models->count() > 0)
                <li>
                    <a href="#models" data-toggle="tab" title="{{ trans('general.asset_models') }}">{{ trans('general.asset_models') }}
                        <badge class="badge badge-secondary"> {{ $models->count()}}</badge>
                    </a>                   
                </li>
                @endif
            </ul>
            <div class="tab-content">
                @if ($assets->count() > 0)
                <div class="tab-pane fade in active" id="assets">
                    <div class="row">
                        <div class="col-md-12">
                                <div class="table-responsive">
                                    <table
                                        data-click-to-select="true"
                                        data-cookie-id-table="requestableAssetsListingTable"
                                        data-pagination="true"
                                        data-id-table="requestableAssetsListingTable"
                                        data-search="true"
                                        data-side-pagination="server"
                                        data-show-columns="true"
                                        data-show-export="false"
                                        data-show-footer="false"
                                        data-show-refresh="true"
                                        data-sort-order="asc"
                                        data-sort-name="name"
                                        data-toolbar="#assetsBulkEditToolbar"
                                        data-bulk-button-id="#bulkAssetEditButton"
                                        data-bulk-form-id="#assetsBulkForm"
                                        id="assetsListingTable"
                                        class="table table-striped snipe-table"
                                        data-url="{{ route('api.assets.requestable', ['requestable' => true]) }}">

                                        <thead>
                                            <tr>
                                                <th class="col-md-1" data-field="image" data-formatter="imageFormatter" data-sortable="true">{{ trans('general.image') }}</th>
                                                <th class="col-md-2" data-field="asset_tag" data-sortable="true" >{{ trans('general.asset_tag') }}</th>                                                
                                                <th class="col-md-2" data-field="model" data-sortable="true">{{ trans('admin/hardware/table.asset_model') }}</th>
                                                <th class="col-md-2" data-field="model_number" data-sortable="true">{{ trans('admin/models/table.modelnumber') }}</th>
                                                <th class="col-md-2" data-field="name" data-sortable="true">{{ trans('admin/hardware/form.name') }}</th>
                                                <th class="col-md-3" data-field="serial" data-sortable="true">{{ trans('admin/hardware/table.serial') }}</th>
                                                <th class="col-md-2" data-field="location" data-sortable="true">{{ trans('admin/hardware/table.location') }}</th>
                                                <th class="col-md-2" data-field="status" data-sortable="true">{{ trans('admin/hardware/table.status') }}</th>
                                                <th class="col-md-2" data-field="expected_checkin" data-formatter="dateDisplayFormatter" data-sortable="true">{{ trans('admin/hardware/form.expected_checkin') }}</th>

                                                @foreach(\App\Models\CustomField::get() as $field)
                                                    @if (($field->field_encrypted=='0') && ($field->show_in_requestable_list=='1'))
                                                        <th class="col-md-2" data-field="custom_fields.{{ $field->db_column }}" data-sortable="true">{{ $field->name }}</th>
                                                    @endif
                                                @endforeach
                                                <th class="col-md-1" data-formatter="assetRequestActionsFormatter" data-field="actions" data-sortable="false">{{ trans('table.actions') }}</th>
                                            </tr>
                                        </thead>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif

                @if ($models->count() > 0)
                <div class="tab-pane fade in {{ ($assets->count() == 0) ? 'active' : '' }}" id="models">
                    <div class="row">
                        <div class="col-md-12">
                                <table
                                        name="requested-assets"
                                        data-toolbar="#toolbar"
                                        class="table table-striped snipe-table"
                                        id="table"
                                        data-advanced-search="true"
                                        data-id-table="advancedTable"
                                        data-cookie-id-table="requestableAssets">
                                <thead>
                                    <tr role="row">
                                        <th class="col-md-1" data-sortable="true">{{ trans('general.image') }}</th>
                                        <th class="col-md-6" data-sortable="true">{{ trans('admin/hardware/table.asset_model') }}</th>
                                        <th class="col-md-3" data-sortable="true">{{ trans('admin/accessories/general.remaining') }}</th>

                                        <th class="col-md-2 actions" data-sortable="false">{{ trans('table.actions') }}</th>
                                    </tr>
                                </thead>

                                <tbody>
                                    @foreach($models as $requestableModel)
                                        <tr>

                                                <td>

                                                    @if ($requestableModel->image)
                                                        <a href="{{ config('app.url') }}/uploads/models/{{ $requestableModel->image }}" data-toggle="lightbox" data-type="image">
                                                            <img src="{{ config('app.url') }}/uploads/models/{{ $requestableModel->image }}" style="max-height: {{ $snipeSettings->thumbnail_max_h }}px; width: auto;" class="img-responsive">
                                                        </a>
                                                    @endif

                                                </td>

                                                <td>
                                                    @can('view', \App\Models\AssetModel::class)
                                                        <a href="{{ route('models.show', ['model' => $requestableModel->id]) }}">{{ $requestableModel->name }}</a>
                                                    @else
                                                        {{ $requestableModel->name }}
                                                    @endcan
                                                </td>

                                                <td>{{$requestableModel->assets->where('requestable', '1')->count()}}</td>

                                                <td>
                                                    <form id="request-asset-model-{{$requestableModel->id}}" action="{{ route('account/request-item', ['itemType' => 'asset_model', 'itemId' => $requestableModel->id])}}" method="POST" accept-charset="utf-8">
                                                        {{ csrf_field() }}
                                                    <input type="text" style="width: 70px; margin-right: 10px;" class="form-control pull-left" name="request-quantity" value="" placeholder="{{ trans('general.qty') }}">
                                                    @if ($requestableModel->isRequestedBy(Auth::user()))
                                                        {{ Form::submit(trans('button.cancel'), ['class' => 'btn btn-danger btn-sm'])}}
                                                    @else
                                                        {{ Form::submit(trans('button.request'), ['class' => 'btn btn-primary btn-sm', 'data-toggle' => "modal", 'data-target' => "#requestAssetModelModal", 'onclick' => 'localStorage.setItem(\'requestedAsset\', ' . $requestableModel->id . '); return false;' ])}}
                                                    @endif
                                                    </form>
                                                </td>
                                        </tr>

                                    @endforeach
                                </tbody>
                            </table>

                        </div>
                    </div>
                </div>
                @endif

            </div> <!-- .tab-content-->
        </div> <!-- .nav-tabs-custom -->

        @endif
    </div> <!-- .col-md-12> -->
</div> <!-- .row -->

<!-- Modal -->
<div class="modal fade" id="requestAssetModal" tabindex="-1" role="dialog" aria-labelledby="requestAssetModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="requestAssetModalLabel">Request Asset with Reason</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form action="" method="POST">
                    <div class="alert alert-danger" id="modal_error_msg" style="display:none">
                        Please enter a valid Reason for Request.
                    </div>
                    <div class="dynamic-form-row" style="min-height: 100px;">
                        <div class="col-md-4 col-xs-12"><label for="modal-name">Reason for Request</label></div>
                        <div class="col-md-8 col-xs-12 required">
                            <textarea class="col-md-6 form-control" id="request-asset-reason" name="reason" rows="4" cols="50"></textarea>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary request-asset-submit">Submit Request</button>
            </div>
        </div>
    </div>
</div>


<!-- Modal -->
<div class="modal fade" id="requestAssetModelModal" tabindex="-1" role="dialog" aria-labelledby="requestAssetModelModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="requestAssetModalLabel">Request Asset Model with Reason</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form action="" method="POST">
                    <div class="alert alert-danger" id="modal_error_msg" style="display:none">
                        Please enter a valid Reason for Request.
                    </div>
                    <div class="dynamic-form-row" style="min-height: 100px;">
                        <div class="col-md-4 col-xs-12"><label for="modal-name">Reason for Request</label></div>
                        <div class="col-md-8 col-xs-12 required">
                            <textarea class="col-md-6 form-control" id="request-asset-model-reason" name="reason" rows="4" cols="50"></textarea>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary request-asset-model-submit">Submit Request</button>
            </div>
        </div>
    </div>
</div>

@stop


@section('moar_scripts')
    @include ('partials.bootstrap-table', [
        'exportFile' => 'requested-export',
        'search' => true,
        'clientSearch' => true,
    ])


    <script nonce="{{ csrf_token() }}">

    $( "a[name='Request']").click(function(event) {
        // event.preventDefault();
        quantity = $(this).closest('td').siblings().find('input').val();
        currentUrl = $(this).attr('href');
        // $(this).attr('href', currentUrl + '?quantity=' + quantity);
        // alert($(this).attr('href'));
    });

    $(document).ready(function () {
        $(".request-asset-submit").on('click', function(e) {
           var reason = $("#request-asset-reason").val();
           if (reason.length === 0) {
               $("#modal_error_msg").show();
               return false;
           } else {
               $("#modal_error_msg").hide();
           }
           var requestedAsset = localStorage.getItem('requestedAsset');
           var form = $("#request-asset-" + requestedAsset);
            $("<input>").attr({
                name: "reason",
                id: "reason",
                type: "hidden",
                value: reason
            }).appendTo(form);
            form.submit();
        });
        $(".request-asset-model-submit").on('click', function(e) {
            var reason = $("#request-asset-model-reason").val();
            if (reason.length === 0) {
                $("#modal_error_msg").show();
                return false;
            } else {
                $("#modal_error_msg").hide();
            }
            var requestedAsset = localStorage.getItem('requestedAsset');
            var form = $("#request-asset-model-" + requestedAsset);
            $("<input>").attr({
                name: "reason",
                id: "reason",
                type: "hidden",
                value: reason
            }).appendTo(form);
            form.submit();
        });
    });
</script>
@stop


