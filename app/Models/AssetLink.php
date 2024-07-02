<?php

namespace App\Models;

use App\Models\Traits\Searchable;
use App\Presenters\Presentable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Watson\Validating\ValidatingTrait;

/**
 * Model for Asset Models. Asset Models contain higher level
 * attributes that are common among the same type of asset.
 *
 * @version    v1.0
 */
class AssetLink extends SnipeModel
{
    use HasFactory;
    protected $presenter = \App\Presenters\AssetModelLinkPresenter::class;
    use Loggable, Requestable, Presentable;

    protected $table = 'model_links';
    protected $hidden = [];

    // Declare the rules for the model validation
    protected $rules = [
        'model_id'       => 'required|integer|exists:models,id',
        'related_model_id'       => 'required|integer|exists:models,id',
    ];

    /**
     * Whether the model should inject it's identifier to the unique
     * validation rules before attempting validation. If this property
     * is not set in the model it will default to true.
     *
     * @var bool
     */
    protected $injectUniqueIdentifier = true;
    use ValidatingTrait;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'model_id',
        'related_model_id',
    ];

    use Searchable;

    /**
     * The attributes that should be included when searching the model.
     *
     * @var array
     */
    protected $searchableAttributes = [
        'model_id',
        'related_model_id',
    ];

    /**
     * The relations and their attributes that should be included when searching the model.
     *
     * @var array
     */
    protected $searchableRelations = [
        'model' => ['name'],
        // 'depreciation' => ['name'],
        // 'category'     => ['name'],
        // 'manufacturer' => ['name'],
    ];

    /**
     * Establishes the model -> model relationship
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     * @since [v1.0]
     * @return \Illuminate\Database\Eloquent\Relations\Relation
     */
    public function assets()
    {
        return $this->hasMany(\App\Models\AssetModel::class, 'related_model_id');
    }


    /**
     * Establishes the asset_model_link -> model relationship
     */
    public function model()
    {
        return $this->belongsTo(\App\Models\AssetModel::class, 'model_id');
    }


    /**
     * Establishes the asset_model_link -> model relationship
     */
    public function related_model()
    {
        return $this->belongsTo(\App\Models\AssetModel::class, 'related_model_id');
    }

    /**
     * -----------------------------------------------
     * BEGIN QUERY SCOPES
     * -----------------------------------------------
     **/

    /**
     * scopeInCategory
     * Get all models that are in the array of category ids
     *
     * @param       $query
     * @param array $categoryIdListing
     *
     * @return mixed
     * @author  Vincent Sposato <vincent.sposato@gmail.com>
     * @version v1.0
     */
    public function scopeInCategory($query, array $categoryIdListing)
    {
        return $query->whereIn('category_id', $categoryIdListing);
    }

    /**
     * scopeRequestable
     * Get all models that are requestable by a user.
     *
     * @param       $query
     *
     * @return $query
     * @author  Daniel Meltzer <dmeltzer.devel@gmail.com>
     * @version v3.5
     */
    public function scopeRequestableModels($query)
    {
        return $query->where('requestable', '1');
    }

    /**
     * Query builder scope to search on text, including catgeory and manufacturer name
     *
     * @param  Illuminate\Database\Query\Builder  $query  Query builder instance
     * @param  text                              $search      Search term
     *
     * @return Illuminate\Database\Query\Builder          Modified query builder
     */
    public function scopeSearchByManufacturerOrCat($query, $search)
    {
        return $query->where('models.name', 'LIKE', "%$search%")
            ->orWhere('model_number', 'LIKE', "%$search%")
            ->orWhere(function ($query) use ($search) {
                $query->whereHas('category', function ($query) use ($search) {
                    $query->where('categories.name', 'LIKE', '%'.$search.'%');
                });
            })
            ->orWhere(function ($query) use ($search) {
                $query->whereHas('manufacturer', function ($query) use ($search) {
                    $query->where('manufacturers.name', 'LIKE', '%'.$search.'%');
                });
            });
    }

    /**
     * Query builder scope to order on manufacturer
     *
     * @param  \Illuminate\Database\Query\Builder  $query  Query builder instance
     * @param  text                              $order       Order
     *
     * @return \Illuminate\Database\Query\Builder          Modified query builder
     */
    public function scopeOrderManufacturer($query, $order)
    {
        return $query->leftJoin('manufacturers', 'models.manufacturer_id', '=', 'manufacturers.id')->orderBy('manufacturers.name', $order);
    }

    /**
     * Query builder scope to order on category name
     *
     * @param  \Illuminate\Database\Query\Builder  $query  Query builder instance
     * @param  text                              $order       Order
     *
     * @return \Illuminate\Database\Query\Builder          Modified query builder
     */
    public function scopeOrderCategory($query, $order)
    {
        return $query->leftJoin('categories', 'models.category_id', '=', 'categories.id')->orderBy('categories.name', $order);
    }

    public function scopeOrderFieldset($query, $order)
    {
        return $query->leftJoin('custom_fieldsets', 'models.fieldset_id', '=', 'custom_fieldsets.id')->orderBy('custom_fieldsets.name', $order);
    }

    /**
     * Query builder scope to order on manufacturer
     *
     * @param  \Illuminate\Database\Query\Builder  $query  Query builder instance
     * @param  text                              $order       Order
     *
     * @return \Illuminate\Database\Query\Builder          Modified query builder
     */
    public function scopeOrderModel($query, $order)
    {
        return $query->leftJoin('models', 'model_links.model_id', '=', 'models.id')->orderBy('models.name', $order);
    }


    /**
     * Checks if the model is deletable
     *
     * @author A. Gianotto <snipe@snipe.net>
     * @since [v6.3.4]
     * @return bool
     */
    public function isDeletable()
    {
        return true;
    }
}
