<script nonce="{{ csrf_token() }}">

    // create the assigned assets listing box for the right side of the screen
    $(function() {
        $('#assigned_assets_select').on("change",function () {
            var assetIds = $('#assigned_assets_select').val();
            console.log(assetIds);
            if (assetIds == '') {
                console.warn('no assets selected');
                $('#related_assets_box').fadeOut();
                $('#related_assets_content').html("");
            } else {
                $.ajax({
                    type: 'GET',
                    url: '{{ config('app.url') }}/api/v1/hardware/related/' + assetIds,
                    headers: {
                        "X-Requested-With": 'XMLHttpRequest',
                        "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr('content')
                    },

                    dataType: 'json',
                    success: function (data) {
                        $('#related_assets_box').fadeIn();

                        var table_html = '<div class="row">';
                        table_html += '<div class="col-md-12">';
                        table_html += '<table class="table table-striped">';
                        table_html += '<thead><tr>';
                        table_html += '<th></th>';
                        table_html += '<th>{{ trans('general.asset_model') }}</th>';
                        table_html += '<th>{{ trans('admin/hardware/form.name') }}</th>';
                        table_html += '<th>{{ trans('admin/hardware/form.tag') }}</th>';
                        table_html += '<th>{{ trans('admin/hardware/form.serial') }}</th>';
                        table_html += '<th>Actions</th>';
                        table_html += '</tr></thead><tbody>';

                        $('#related_assets_content').append('');

                        if (data.rows.length > 0) {

                            for (var i in data.rows) {
                                var asset = data.rows[i];
                                table_html += '<tr>';
                                if (asset.image != null) {
                                    table_html += '<td class="col-md-1"><a href="' + asset.image + '" data-toggle="lightbox" data-type="image"><img src="' + asset.image + '" style="max-height: {{ $snipeSettings->thumbnail_max_h }}px; width: auto;"></a></td>';
                                } else {
                                    table_html += "<td></td> ";
                                }
                                table_html += '<td><a href="{{ config('app.url') }}/hardware/' + asset.id + '">';
                                table_html += " " + asset.model.name;
                                table_html += '</td>';
                                table_html += '<td><a href="{{ config('app.url') }}/hardware/' + asset.id + '">';

                                if ((asset.name == '') && (asset.name != null)) {
                                    table_html += " " + asset.model.name;
                                } else {
                                    table_html += asset.name;
                                    table_html += " (" + asset.model.name + ")";
                                }

                                table_html += '</a></td>';
                                table_html += '<td class="col-md-2">' + asset.asset_tag + '</td>';
                                table_html += '<td class="col-md-1">' + asset.serial + '</td>';
                                table_html += '<td class="col-md-1"><button class="btn btn-sm btn-primary add-related-asset" data-id="' + asset.id + '" data-asset-tag="' + asset.asset_tag + '">Add</button></td>';
                                table_html += "</tr>";
                            }
                        } else {
                            table_html += '<tr><td colspan="4">No related assets</td></tr>';
                        }
                        $('#related_assets_content').html(table_html + '</tbody></table></div></div>');
                        $('.add-related-asset').click(function() {
                            $("#assigned_assets_select").select2("trigger", "select", {data: { id: $(this).data('id'), text: $(this).data('asset-tag') }});
                            $(this).parent().parent().remove();
                        });

                    },
                    error: function (data) {
                        $('#related_assets_box').fadeOut();
                    }
                });
            }
        });
    });
</script>
<style>
    .select2-container--default .select2-selection--multiple .select2-selection__choice {
        color: black !important;
    }
</style>
