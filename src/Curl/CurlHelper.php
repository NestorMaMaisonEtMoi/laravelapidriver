<?php
namespace Nestor\LaravelApidriver\Curl;

use App\Models\User;
use http\Env;

trait CurlHelper
{
    /**
     * Indicates if http request body filtering is enable
     *
     * @var bool
     */
    protected $isFilter = true;

    /**
     * Request header
     *
     * @var array
     */
    protected $header;

    /**
     * Request special tag use to get privacy data
     *
     * @var array
     */
    protected $specialTag;

    /**
     * Status code of http response
     *
     * @var bool
     */
    protected $httpStatusCode = 0;

    /**
     * Target url
     *
     * @var string
     */
    protected $url;

    /**
     * Set specialTag
     *
     * @return static
     */
    public function setSpecialTag(string $tag)
    {
        $this->specialTag = $tag;
        return $this;
    }

    /**
     * Set the request header
     *
     * @return static
     */
    protected function setHeader(array $header) : Curl
    {
        $this->header = $header;
    }

    /**
     * Reset the request header
     *
     * @return static
     */
    protected function resetHeader() : Curl
    {
        $this->header = [];
        return $this;
    }

    /**
     * Get the request header
     *
     * @param int  $type (1: GET, 2: POST, 3 PUT, 4 DELETE)
     * @return array
     */
    protected function getHeader(int $type = 1) : array
    {
        if (empty($this->header) || $type == 2 || $type == 3 || ! empty($this->specialTag)) {
            $this->generateHeader();
            $this->clearSpecialTag();
        }
        return $this->header ?? [];
    }

    /**
     * Generate the request header
     *
     * @param int  $type (1: GET, 2: POST, 3 PUT, 4 DELETE)
     * @return array
     */
    protected function generateHeader(int $type = 1) : array
    {
        //dd( env('APP_NAME', 'RIEN') );
        $tokenTest = 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJjb25zdHJ1Y3RldXJJZCI6IlRSRUNPQkFUIiwiY29uc3RydWN0ZXVyTm9tIjoiVHJlY29iYXQiLCJhY2Nlc1R5cGUiOiJBUEkiLCJiZGRJZCI6Im5lc3RvcmJ5dHJlY29iYXR2MiIsImVudGl0ZSI6IkNPTlNUUlVDVEVVUiJ9.ntZugqpakmbrLtUUvAAi2zFirdERRJgThHaJd9tJ70A';
        $token = session()->exists( "apiToken" ) ? session("apiToken") : null;

        if( $token == null ){
            dd( "Le token Api n'est pas initialisé dans la session sous la key : apiToken" );
        }
        //dd( $token );
        //echo( "<script> MONTEST(" . session("apiToken") . ") </script>"  );
        $this->header =null;
        $isIp = $type == 2 || $type == 3;
        $headers = array_filter([
            'Content-Type' => 'application/json',
            'Authorization' => 'Bearer ' . $token,
            'user-token' => function_exists('userId') ? userId() : null,
            'api' => request()->path(),
            'special' => $this->specialTag,
            'i-debug' => env('API_DEBUG_CODE', 0),
            'client-ip-address' => $isIp ? ip_address() : 0,
            'client-user-agent' => $isIp ? user_agent() : 0,
        ]);
        foreach ($headers as $key => $value) {
            $this->header[] = $key . ':' . $value;
        }
        //dd( $this->header );
        return $this->header;
    }

    /**
     * Clear specialTag
     *
     * @return void
     */
    protected function clearSpecialTag()
    {
        $this->specialTag = null;
    }

    /**
     * Build the http query for GET request
     *
     * @return string
     */
    protected function buildQuerry(array $query = []) : string
    {
        return http_build_query($this->filter($query));
    }

    /**
     * Filter the input to ignore null value
     *
     * @return array
     */
    protected function filter(array $input) : array
    {
        if ($this->isFilter) {
            foreach ($input as $key => $value) {
                if (! is_null($value)) {
                    $res[$key] = $value;
                }
            }
            return $res ?? [];
        } else {
            return $input;
        }
    }

    /**
     * Save the status code of http response
     *
     * @return void
     */
    protected function setStatusCode(int $code)
    {
        $this->httpStatusCode = $code;
    }
}
