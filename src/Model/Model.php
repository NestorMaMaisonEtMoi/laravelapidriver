<?php

namespace Nestor\LaravelApidriver\Model;

use Illuminate\Database\Eloquent\Model as BaseModel;
use Nestor\LaravelApidriver\Connection\ApiConnection;
use Nestor\LaravelApidriver\Eloquent\Builder;
use Nestor\LaravelApidriver\Query\Builder as QueryBuilder;

abstract class Model extends BaseModel
{

    /**
     * @author Pierre-Yves
     * Attribut permettant de déterminer si une instance de Model et actif ou pas
     * @var null
     */
    protected $actif = null;

     /**
     * @author Pierre-Yves
     * Attribut permettant de déterminer si l'objet est propre a nestor. Permet de ne pas donner certain droit
     * @var null
     */
    protected $privateNestor = null;
    
    /**
     * Retourne si oui ou non l'instance est active ou pas
     * @return mixed
     * @throws \Exception
     */
    public function isActif()
    {
        //Si le champs actif est pas défini, on return true.
        if( $this->actif == null ) return true;
        return $this[ $this->actif ];
    }

    
    /**
     * Retourne le nom du champs actif du model
     * @return null
     */
    public function getChampActif(){
        return $this->actif;
    }

    /**
     * Retourne si oui ou non l'instance est propre à nestor ou pas
     * @return mixed
     * @throws \Exception
     */
    public function isPrivateNestor()
    {
        //Si le champs privateNestor n'est pas défini, on return false.
        if( $this->privateNestor == null ) return false;
        return $this[ $this->privateNestor ];
    }

    /**
     * Retourne le nom du champs private Nestor du model
     * @return null
     */
    public function getChampPrivateNestor(){
        return $this->privateNestor;
    }    

    protected $guarded = array();

    /**
     * Affect la primaryKey en tant que ID, necessaire pour les get by id, delete et put sur l'API
     * @return mixed
     */
    //abstract public function initPrimaryKey();
    public function initPrimaryKey(){
        $this->id = $this->getId();
    }


    /**
     * @inheritDoc Illuminate\Database\Eloquent\Concerns\HasTimestamps
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

     /**
     * Create a new Eloquent query builder for the model.
     *
     * @param  \Illuminate\Database\Query\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder|static
     */
    public function newEloquentBuilder($query)
    {
        return new Builder($query);
    }

    /**
     * Get a new query builder instance for the connection.
     *
     * @return \Illuminate\Database\Query\Builder
     */
    protected function newBaseQueryBuilder()
    {
        $conn = $this->getConnection();

        //custom
        if( $conn instanceof ApiConnection){
            $conn->setModel(static::class);
        }

        $grammar = $conn->getQueryGrammar();

        return new QueryBuilder($conn, $grammar, $conn->getPostProcessor());
    }

    /**
     * Get the table qualified key name.
     *
     * @return string
     */
    public function getQualifiedKeyName()
    {
        return $this->getKeyName();
    }

     /**
     * Validate attributes before return it via toArray method or create a model instance of it
     *
     * @return bool
     */
    public function validate() : bool
    {
        return (empty($this->is_valid)) ? true : $this->is_valid == 1;
    }

    /**
     * Create a collection of models from plain arrays.
     *
     * @param  array  $items
     * @param  string|null  $connection
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public static function hydrate(array $items, $connection = null)
    {
        $instance = (new static)->setConnection($connection);

        if (! empty($items['total'])) {
            $meta = $items;
            $items = $items['data'] ?? [];
            unset($meta['data']);
        }

        $items = array_map(function ($item) use ($instance) {
            return $instance->newFromBuilder($item);
        }, $items);

        if (! empty($meta)) {
            $meta = $instance->newCollection($meta);
            $meta['data'] = $items;
        }

        return empty($meta) ? $instance->newCollection($items) : $meta;
    }
}
