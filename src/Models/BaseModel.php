<?php

namespace Riari\Forum\Models;

use App;
use Cache;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Riari\Forum\Forum;

abstract class BaseModel extends Model
{
    /**
     * @var Router
     */
    protected $router;

    /**
     * Create a new model instance.
     *
     * @param  array  $attributes
     */
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->forceDeleting = !config('forum.preferences.soft_deletes');
        $this->router = App::make('Illuminate\Routing\Router');
    }

    /*
    |--------------------------------------------------------------------------
    | Attributes
    |--------------------------------------------------------------------------
    */

    public function getPostedAttribute()
    {
        return $this->created_at->diffForHumans();
    }

    public function getUpdatedAttribute()
    {
        return $this->updated_at->diffForHumans();
    }

    public function getDeletedAttribute()
    {
        return !is_null($this->deleted_at) ? 1 : 0;
    }

    protected function rememberAttribute($item, $function)
    {
        $cacheItem = get_class($this).$this->id.$item;
        $value = Cache::remember($cacheItem, config('forum.preferences.cache_lifetime'), $function);
        return $value;
    }

    /*
    |--------------------------------------------------------------------------
    | Helpers
    |--------------------------------------------------------------------------
    */

    /**
     * Build a named route using the parameters set by the model.
     *
     * @param  string  $name
     * @param  array  $extraParameters
     * @return string
     */
    public function buildRoute($name, $extraParameters = [])
    {
        $parameterNames = array_flip($this->router->getRoutes()->getByName($name)->parameterNames());
        $parameters = array_intersect_key($this->getRouteParameters(), $parameterNames);

        return route($name, array_merge($parameters, $extraParameters));
    }

    /**
     * Determine if this model has been updated since the given model.
     *
     * @param  Model  $model
     * @return boolean
     */
    public function updatedSince(&$model)
    {
        return ($this->updated_at > $model->updated_at);
    }

    /**
     * Determine if this model has been updated.
     *
     * @return boolean
     */
    public function hasBeenUpdated()
    {
        return ($this->updated_at > $this->created_at);
    }

    /**
     * Toggle an attribute on this model.
     *
     * @param  string  $attribute
     * @return void
     */
    public function toggle($attribute)
    {
        $this->$attribute = !$this->$attribute;
        $this->save();
    }
}
