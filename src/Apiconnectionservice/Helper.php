<?php

namespace Nestor\Apiconnectionservice;

use Nestor\Curl\Curl;

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
        $tag = $this->getTag($input);

        return $this->getCurl()
            ->setSpecialTag($tag)
            ->put($api, $ids, $this->isValid($input));
    }

    protected function deleteById(string $api, int $id)
    {
        $this->getCurl()->delete($api, $id);
    }

    protected function getCurl()
    {
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
