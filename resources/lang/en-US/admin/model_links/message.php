<?php

return array(

    'deleted' => 'Deleted asset model',
    'does_not_exist' => 'Model Link does not exist.',
    'no_association' => 'WARNING! The asset Model Link for this item is invalid or missing!',
    'no_association_fix' => 'This will break things in weird and horrible ways. Edit this asset now to assign it a model.',
    'assoc_users'	 => 'This Model Link is currently associated with one or more assets and cannot be deleted. Please delete the assets, and then try deleting again. ',


    'create' => array(
        'error'   => 'Model Link was not created, please try again.',
        'success' => 'Model Link created successfully.',
        'duplicate_set' => 'An asset Model Link with that name, manufacturer and Model Link number already exists.',
    ),

    'update' => array(
        'error'   => 'Model Link was not updated, please try again',
        'success' => 'Model Link updated successfully.',
    ),

    'delete' => array(
        'confirm'   => 'Are you sure you wish to delete this asset model?',
        'error'   => 'There was an issue deleting the model. Please try again.',
        'success' => 'The Model Link was deleted successfully.'
    ),

    'restore' => array(
        'error'   		=> 'Model Link was not restored, please try again',
        'success' 		=> 'Model Link restored successfully.'
    ),

    'bulkedit' => array(
        'error'   		=> 'No fields were changed, so nothing was updated.',
        'success' 		=> 'Model Link successfully updated. |:model_count models successfully updated.',
        'warn'          => 'You are about to update the properties of the following model:|You are about to edit the properties of the following :model_count models:',

    ),

    'bulkdelete' => array(
        'error'   		    => 'No models were selected, so nothing was deleted.',
        'success' 		    => 'Model Link deleted!|:success_count models deleted!',
        'success_partial' 	=> ':success_count model(s) were deleted, however :fail_count were unable to be deleted because they still have assets associated with them.'
    ),

);
