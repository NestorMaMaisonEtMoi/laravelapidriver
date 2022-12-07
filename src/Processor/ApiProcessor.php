<?php

namespace Nestor\LaravelApidriver\Processor;

use Illuminate\Database\Query\Grammars\Grammar;
use Illuminate\Database\Query\Processors\Processor;
use Illuminate\Database\Query\Builder;

class ApiProcessor extends Processor
{
  /**
     * Process an  "insert get ID" query.
     *
     * @param  \Illuminate\Database\Query\Builder  $query
     * @param  string  $sql
     * @param  array   $values
     * @param  string  $sequence
     * @return int
     */
    public function processInsertGetId(Builder $query, $sql, $values, $sequence = null)
    {
        debugbar()->debug( "--------------> processInsertGetId sur mon api en cours ..." );
        debugbar()->debug( "-------------->sequence : $sequence" );
        $response = $query->getConnection()->insert($sql, $values);
        
        //$id = $response['id'] ?? 0;
        $id = $response[ ($sequence == null ? 'id':$sequence) ] ?? 0;
        debugbar()->debug( "--------------> processInsertGetId sur mon api OK. " . $id );
      
        return is_numeric($id) ? (int) $id : $id;
    }
}
