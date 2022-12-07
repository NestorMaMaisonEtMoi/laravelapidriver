<?php

namespace Nestor\LaravelApidriver\Apiconnectionservice;

use Nestor\LaravelApidriver\Curl\Curl;

trait Helper
{
    protected $curl;

    protected function getTag(array $input = []) : string
    {
        $tag = $input['tag'] ?? '';
        unset($input['tag']);
        return $tag;
    }

    protected function get(string $api, array $input = [], $isGetMetaData = false) : array
    {
        //dd( "Helper.php => API : " . $api . " PARAM => " . json_encode( $input ) );
        $tag = $this->getTag($input);

        return $this->getCurl()
            ->setSpecialTag($tag)
            ->get($api, $this->isValid($input), $isGetMetaData);
    }

    protected function post(string $api, array $input = []) : array
    {
        $tag = $this->getTag($input);

        return $this->getCurl()
            ->setSpecialTag($tag)
            ->post($api, $this->isValid($input));
    }

    protected function put(string $api, $ids, array $input = []) : array
    {
        //dd( "ApiconnectionService/Helper Je fais un PUT" );
        $tag = $this->getTag($input);

        return $this->getCurl()
            ->setSpecialTag($tag)
            ->put($api, $ids, $this->isValid($input));
    }

    protected function deleteById(string $api, $id)
    {
        $this->getCurl()->delete($api, $id);
    }

    protected function getCurl()
    {
        debugbar()->debug( "HOST : " . $this->config['host'] );
        if (empty($this->curl)) {
            $this->curl = new Curl($this->config['host']);
        }
        return $this->curl;
    }

    protected function isValid(array &$input) : array
    {
        /*if (is_array(head($input))) {
            foreach ($input as $key => $record) {
                if (is_null($input[$key]['is_valid'] ?? null)) {
                    $input[$key]['is_valid'] = 1;
                }
            }
        } elseif (is_null($input['is_valid'] ?? null)) {
            $input['is_valid'] = 1;
        }*/

        return $input;
    }
}
