<?php

namespace Nestor\LaravelApidriver\Connection;

use Illuminate\Database\Connection;
use Illuminate\Support\Arr;
use Nestor\LaravelApidriver\Apiconnectionservice\Service;
use Nestor\LaravelApidriver\Grammar\ApiGrammar;
use Nestor\LaravelApidriver\Processor\ApiProcessor;

class ApiConnection extends Connection
{
    use Service;

    /**
     * Run a select statement against the api.
     *
     * @param  array  $query
     * @param  array  $bindings
     * @param  bool  $useReadPdo
     * @return array
     */
    public function select($query, $bindings = [], $useReadPdo = true)
    {
        $selectById = false;
        $selectForDatatable = false;

        //dd($this->getModel()->getPrimaryKey());
        //dd($query);
        //dd($query["id"]);
        if( isset( $query["id"] ) && $query["limit"] == "1" && isset( $query["id"] ) ){
            //dd("Recherche par Id");
            $selectById = true;
            $query["api"] = $query["api"]. '/' . $query["id"];
        }elseif( isset( $query["limit"] ) && $query["limit"] > "1"  ){
            //dd("Recherche par Id");
            $selectForDatatable = true;
        }else{
            $selectForDatatable = false;
        }

        //dd($selectById);
        //dd( "ApiConnection.php => API : " . json_encode( $query )  . " PARAM => " . json_encode( $bindings ) );
        //dd( $bindings[1] );
        foreach ( $bindings as $binding ){
            if( strpos( $binding, '=' )  ){
                //dd( $binding );
                $_split = explode( "=", $binding );
                $query[$_split[0]] = $_split[1];
            }

        }
        //dd( "ApiConnection.php => API : " . json_encode( $query )  . " PARAM => " . json_encode( $bindings ) );
        if (empty($query) || empty($query['api'])) {
            return [];
        }

        // Get api string from query and unset it from query
        $api = $query['api'];
        unset($query['api']);

        // Get flag for get metadata and unset it from query
        $isGetMetaData = ! empty($query['isGetMetaData']) && $query['isGetMetaData'] == 1 ? true : false;
        unset($query['isGetMetaData']);

        // Execute get request from api and receive response data
        $data = $this->get($api, $query, $isGetMetaData);
        //dd( $data );
        // Check flag for get metadata
        if ( $isGetMetaData || (Arr::exists( $data, 'data' ) && $selectForDatatable) ) {
            //dd( $data );
            $res['total'] = $data['total'];
            $res['totalFiltre'] = $data['totalFiltre'];
            $res['start'] = $data['start'];
            $res['length'] = $data['length'];
            /*$res['current_page'] = $data['current_page'];
            $res['last_page'] = $data['last_page'];
            $res['next_page_url'] = $data['next_page_url'];
            $res['prev_page_url'] = $data['prev_page_url'];
            $res['from'] = $data['from'];
            $res['to'] = $data['to'];*/
            $data = $data['data'];
        }elseif ( $selectForDatatable == false && $selectById == false ){
            if( Arr::exists( $data, 'data' ) == false ) {
                debugbar()->error("Impossible de trouver DATA dans la réponse. Réponse de l'api pour : $api " .  json_encode( $query )  . " $isGetMetaData");
                debugbar()->error($data);
            }
            $data = $data['data'];
        }
        debugbar()->debug( json_encode( $data ) );
        //dd($data);
        // Validate data and set index
        if (! empty($data)) {
            $attribute = isset($options['index_by']) ? $options['index_by'] : '';
            //dd( $attribute );
            $isIdx = ! empty($attribute);
            //dd( $isIdx );
            try {
                //dd($data);
                if( $selectById ) {
                    //dd("Modif");
                    $dataTmp[] = $data;
                    //dd($dataTmp);
                    $data = $dataTmp;
                }
                debugbar()->debug( $data );
                //dd($data);
                foreach ($data as $record) {
                    if ($isIdx) {
                        $idx = $record[$attribute] ?? null;
                        if (! empty($idx)) {
                            $res['data'][$idx] = $this->getModel()->fill($record)->toArray();
                        }
                    } else {
                        //dd($record);
                        try {
                            debugbar()->info( $record );
                            $model = $this->getModel()->fill($record);
                        }catch (\Exception $e){
                            dd( $record );
                        }

                        if ($model->validate()) {
                            if( $selectById ){
                                $res['data'] = $model->toArray();
                            }else{
                                $res['data'][] = $model->toArray();
                            }

                        }
                    }
                }
            } catch (\Exception $e) {
                return [];
            }
        }
        //dd($res);
        return ($isGetMetaData || $selectById || $selectForDatatable )  ? $res ?? [] : $res['data'] ?? [];
    }

    /**
     * Run an insert statement against the database.
     *
     * @param  array  $query
     * @param  array   $bindings
     * @return bool
     */
    public function insert($query, $bindings = [])
    {
        if (empty($query) || empty($query['api'])) {
            return [];
        }

        // Set api name then unset it from query array
        $api = $query['api'];
        unset($query['api']);

        // Execute post request and get response
        return $this->post($api, $query) ?? [];
    }

      /**
     * Run an update statement against the database.
     *
     * @param  array  $query
     * @param  array   $bindings
     * @return int
     */
    public function update($query, $bindings = [])
    {
        //dd( "UPDATE - Je vais modifier mon OBJ" );
        //dd( $query );
        if (empty($query) || empty($query['api']) || empty($query['id'])) {
            dd("Il manque un champs api ou id : " . json_encode( $query ) . " binding : " . json_encode( $bindings ) );
            return 0;
        }

        // Get the api name from query, then unset it
        $api = $query['api'];
        unset($query['api']);

        // Get the id value from query, then unset it
        $id = $query['id'];
        unset( $query['id'] );

        //dd( "api =>" . $api . ", id " . $id . ", query => " . json_encode( $query ) );
        //debugbar()->debug( "api =>" . $api . ", id " . $id . ", query => " . json_encode( $query ) );
        // Execute put request and get response
        $res = $this->put($api, $id, $query);
        //dd( $res );
        return empty($res) ? 0 : 1;
    }

    /**
     * Execute mass update
     *
     * @param string $api
     * @param mixed $ids
     * @param array $values
     * @return void
     */
    public function massUpdate(string $api, $ids, array $values)
    {
        if (empty($api)) {
            return [];
        }

        $res = $this->put($api, $ids, $values);
        return $res ?? [];
    }

    /**
     * Execute put & post from incoming models
     *
     * @param string $api
     * @param array $models
     * @return void
     */
    public function batchUpdate(string $api, array $models)
    {
        dd("ApiConnectio  BatchUpdate");
        if (empty($models) || empty($api)) {
            return [];
        }

        $putData = $models['putData'] ?? [];
        $ids = ids($putData);
        $postData = $models['postData'] ?? [];

        $res[] = $this->put($api, $ids, $putData);
        $res[] = $this->post($api, $postData);
        return $res ?? [];
    }

    /**
     * Run a delete statement against the database.
     *
     * @param  string  $query
     * @param  array   $bindings
     * @return int
     */
    public function delete($query, $bindings = [])
    {
        if (empty($query) || empty($query['api']) || empty($query['id'])) {
            return 0;
        }

        $api = $query['api'];
        $id = $query['id'];

        $res = $this->deleteById($api, $id);

        return empty($res) ? 0 : 1;
    }

    /**
     * Get a schema builder instance for the connection.
     *
     * @return \Illuminate\Database\Schema\Builder
     */
    public function getSchemaBuilder()
    {
        return null;
    }

    /**
     * Get the default query grammar instance.
     *
     * @return \App\Database\ApiGrammar
     */
    protected function getDefaultQueryGrammar()
    {
        return $this->withTablePrefix(new ApiGrammar);
    }

    /**
     * Get the default schema grammar instance.
     *
     * @return \Illuminate\Database\Schema\Grammars\Grammar
     */
    protected function getDefaultSchemaGrammar()
    {
        return null;
    }

    /**
     * Get the default post processor instance.
     *
     * @return \App\Database\ApiProcessor
     */
    protected function getDefaultPostProcessor()
    {
        return new ApiProcessor;
    }

    /**
     * Get the Doctrine DBAL driver.
     *
     * @return \Doctrine\DBAL\Driver\PDOMySql\Driver
     */
    protected function getDoctrineDriver()
    {
        return null;
    }
}
