<div id="{{ (isset($id_divname)) ? $id_divname : 'assetLinksBulkEditToolbar' }}" style="min-width:400px">
{{ Form::open([
      'method' => 'POST',
      'route' => ['asset_links/bulkdelete'],
      'class' => 'form-inline',
      'id' => (isset($id_formname)) ? $id_formname : 'assetLinksBulkForm',
 ]) }}

    {{-- The sort and order will only be used if the cookie is actually empty (like on first-use) --}}
    <input name="sort" type="hidden" value="assets.id">
    <input name="order" type="hidden" value="asc">
    <label for="bulk_actions">
        <span class="sr-only">
            {{ trans('button.bulk_actions') }}
        </span>
    </label>
    <select name="bulk_actions" class="form-control select2" aria-label="bulk_actions" style="min-width: 350px;">
        @if((isset($status)) && ($status == 'Deleted'))
        @can('delete', \App\Models\Asset::class)
            <option value="delete">{{ trans('button.delete') }}</option>
        @endcan
        <option value="labels" accesskey="l">{{ trans_choice('button.generate_labels', 2) }}</option>
        @endif
    </select>

    <button class="btn btn-primary" id="{{ (isset($id_button)) ? $id_button : 'bulkAssetEditButton' }}" disabled>{{ trans('button.go') }}</button>
    <button class="btn btn-primary" id="bulkAssetCheckInButton">Quick Check-In</button>
    <button class="btn btn-primary" id="bulkAssetCheckOutButton">Quick Check-Out</button>

    {{ Form::close() }}
</div>
<script type="text/javascript">
    document.addEventListener('DOMContentLoaded', function() {
        $('#bulkAssetCheckInButton').on('click', function() {
            var selectedAssets = $('#assetsListingTable').bootstrapTable('getSelections');
            console.log(selectedAssets);
            localStorage.setItem('selectedAssets', JSON.stringify(selectedAssets));
            window.location.href = 'hardware/bulkcheckin';
            return false;
        });
        $('#bulkAssetCheckOutButton').on('click', function() {
            // Store selected assets in local storage
            var selectedAssets = $('#assetsListingTable').bootstrapTable('getSelections');
            console.log(selectedAssets);
            localStorage.setItem('selectedAssets', JSON.stringify(selectedAssets));
            window.location.href = 'hardware/bulkcheckout';
            return false;
        });
    });
</script>
