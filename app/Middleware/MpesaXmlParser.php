<?php

namespace Noorfarooqy\MpesaLaravel\Middleware;

use Closure;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use SimpleXMLElement;

class MpesaXmlParser
{

    /**
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next, $is_c2b = 0)
    {
        $xml = file_get_contents("php://input");
        // try {

        // } catch (\Throwable $th) {
        //     Log::debug($th->getMessage());
        //     throw new Exception("FAILED TO PARSE XML:: " . $th->getMessage(), 1);
        // }
        $xml_parse = $this->parse($xml);
        $request->merge(['parsed_xml' => $xml_parse]);
        $request->merge(['raw_xml' => $xml]);

        return $next($request);
    }
    public $error_message = '';
    public function parse($body)
    {
        $xml = $body;
        $body = str_replace('\\"', '\'', $body);
        $body = str_replace('\?', '', $body);
        $body = str_replace('\\', '', $body);
        // Log::info('-------------------1---XML----1------------');
        // Log::info($body);
        $body = str_replace("soapenv:", '', $body);
        $body = str_replace('req:', '', $body);
        $body = str_replace('res:', '', $body);
        $body = str_replace(']]>', '', $body);
        $body = str_replace('<Response [200]>', '', $body);

        $body = str_replace('<?xml version="1.0" encoding="UTF-8"?>', '', $body);
        $body = str_replace("<?xml version='1.0' encoding='UTF-8'?>", '', $body);

        $body = str_replace("<?xml version='1.0' encoding='UTF-8'?>", '', $body);
        $body = str_replace('<?xml version="1.0" encoding="UTF-8"?>', '', $body);

        $body = str_replace('xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/"', '', $body);
        $body = str_replace("xmlns:soapenv='http://schemas.xmlsoap.org/soap/envelope/'", '', $body);

        $body = str_replace('xmlns:req="http://api-v1.gen.mm.vodafone.com/mminterface/request"', '', $body);
        $body = str_replace("xmlns:req='http://api-v1.gen.mm.vodafone.com/mminterface/request'", '', $body);

        $body = str_replace('xmlns:res="http://api-v1.gen.mm.vodafone.com/mminterface/result"', '', $body);
        $body = str_replace("xmlns:res='http://api-v1.gen.mm.vodafone.com/mminterface/result'", '', $body);

        $body = str_replace('xmlns="http://api-v1.gen.mm.vodafone.com/mminterface/result"', '', $body);
        $body = str_replace("xmlns='http://api-v1.gen.mm.vodafone.com/mminterface/result'", '', $body);

        $body = str_replace('xmlns="http://api-v1.gen.mm.vodafone.com/mminterface/response"', '', $body);
        $body = str_replace("xmlns='http://api-v1.gen.mm.vodafone.com/mminterface/response'", '', $body);

        //c2b
        $body = str_replace('xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"', '', $body);
        $body = str_replace('xmlns:ns1="http://cps.huawei.com/cpsinterface/c2bpayment"', '', $body);
        $body = str_replace('<?xml version="1.0" encoding="utf-8" ?>', '', $body);
        $body = str_replace('xmlns:c2b="http://cps.huawei.com/cpsinterface/c2bpayment"', '', $body);
        $body = str_replace('ns1:', '', $body);

        $body = str_replace('<![CDATA[<?xml version="1.0" encoding="UTF-8"?>', '', $body);
        $body = str_replace("<![CDATA[<?xml version='1.0' encoding='UTF-8'?>", '', $body);

        $body = str_replace('<![CDATA[', '', $body);
        $body = trim(preg_replace('/\s\s+/', ' ', $body));
        $body = str_replace(' >', '>', $body);
        $body = str_replace('"', '', $body);
        try {
            $xml = simplexml_load_string($body);
            return $xml;
        } catch (\Throwable $th) {
            $this->error_message = $th->getMessage();
            Log::info('[*] MPESA XML PARSE CAUGHT ERROR:- ' . $th->getMessage());
            return new SimpleXMLElement($body);
            // return false;
        }
    }
}
