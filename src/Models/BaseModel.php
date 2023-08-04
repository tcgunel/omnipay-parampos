<?php

namespace Omnipay\Parampos\Models;

use Omnipay\Parampos\Helpers\Helper;
use Omnipay\Parampos\Helpers\XmlDomConstruct;

class BaseModel
{
	public function __construct(?array $abstract)
	{
		foreach ($abstract as $key => $arg) {

            $methodName = 'set' . str_replace('_', '', $key);

            if (method_exists($this, $methodName)){

                $this->$methodName($arg);

            }else if (property_exists($this, $key)) {

				$this->$key = $arg;

			}

		}

		$this->formatFields();
	}

	private function formatFields()
	{
		$fields = ["cardExpireMonth", "cardExpireYear", "threeD", "binNumber", "echo", "gsm", "price"];

		foreach ($fields as $field) {

			if (!empty($this->$field)) {

				$func = "format_{$field}";

				Helper::$func($this->$field, $this->$field);

			}

		}
	}

	public function asXml($root_tag_name)
	{
		if ($root_tag_name) {

			$array = [$root_tag_name => json_decode(json_encode($this), 1)];

		} else {

			$array = json_decode(json_encode($this), 1);

		}

		if (isset($array["auth"]["products"])) {

			$array["auth"]["products"] = ["product" => $array["auth"]["products"]];

		}

		$dom = new XmlDomConstruct('1.0', 'utf-8');

		$dom->fromMixed($array);

		return $dom->saveXML();
	}
}
