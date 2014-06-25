<?php
if (!function_exists('curl_init')) {
	throw new Exception('VdoCipher needs the CURL PHP extension.');
}
if (!function_exists('json_decode')) {
	throw new Exception('VdoCipher needs the JSON PHP extension.');
}
/**
 */
class Vdo{
	/*
	public $title;
	public $description;
	public $upload_time;
	public $file_name;
	public $length;
	public $view_count;
	public $id;
	public $statusText;
	*/

	public function rules()
	{
		//TODO
		return array();
	}

	public function search(){
		//TODO
		return new CDataProvider();
	}

	/**
	 * Declares attribute labels.
	 */
	public function attributeLabels()
	{
		return array(
			'title'=>'Title',
			'description'=>'Description',
			'upload_time'=>'Upload time',
			'file_name'=>'',
			'length'=>'Duration',
			'view_count'=>'Views count',
			'id'=>'Video id',
			'statusText'=>'Status text',
		);
	}

	public function renderPlayer($height, $width){
		$otp = VdoCipher::getOtp($this->id)['otp'];
		echo "<div id='vdo$otp'></div>";
		echo "<script src='https://de122v0opjemw.cloudfront.net/utils/playerInit.php?otp=$otp&height=$height&width=$width' ></script>";
	}

	public static function findByPk($id){
		$vdo = new Vdo;
		$data = VdoCipher::makeRequest('search', array('id'=>$id, 'limit'=>1));
		if (count($data) == 0) {
			throw new CHttpException(404, "Video with given primary key not found.");
		}
		foreach ($data[0] as $key=>$value) {
			$vdo->$key = $value;
		}
		return $vdo;
	}

}


class VdoCipher{
	public static $CURL_OPTS = array(
		CURLOPT_CONNECTTIMEOUT => 10,
		CURLOPT_RETURNTRANSFER => true,
		CURLOPT_TIMEOUT		=> 60,
		CURLOPT_USERAGENT	=> 'vdo-php-1.0',
		//CURLOPT_PROXY			=> 'proxy-host-name:port',
	);
	private static $config = array();

	public function searchByTitle(){
		//$url = self::$intialUrl."searchVideo?".http_build_query($fields);
		$result = VDO::makeRequest($url,self::setInitialParams());
		$otp = VDO::getOtp($result['videoId']);
		echo $otp;
		$vdo = new vdo_video;
		$vdo->otp = $otp;
		return $vdo;
	}

	public static function getOtp($id){
		return self::makeRequest('otp',array("id"=>$id));
	}

	public static function makeRequest($action, $params, $ch=null) {
		$config = parse_ini_file(Yii::app()->extensionPath.'/vdocipher/config.ini', true);
		$initialUrl = $config['main']['apiURL'];
		if (!$ch) {
			$ch = curl_init();
		}
		$url = $initialUrl.$action."?".http_build_query($params);
		$opts = self::$CURL_OPTS;
		$opts[CURLOPT_POSTFIELDS] = 'clientSecretKey='.$config['main']['CLIENT_SECRET_KEY'];
		$opts[CURLOPT_URL] = $url;

		if (isset($opts[CURLOPT_HTTPHEADER])) {
			$existing_headers = $opts[CURLOPT_HTTPHEADER];
			$existing_headers[] = 'Expect:';
			$opts[CURLOPT_HTTPHEADER] = $existing_headers;
		} else {
			$opts[CURLOPT_HTTPHEADER] = array('Expect:');
		}

		curl_setopt_array($ch, $opts);
		$result = curl_exec($ch);

		if ($result === false && empty($opts[CURLOPT_IPRESOLVE])) {
			throw new Exception(curl_error($ch), curl_errno($ch));
		}
		curl_close($ch);
		$result = json_decode($result, true);
		if(isset($result['error'])){
			throw new Exception($result['error']);
		}
		return $result;
	}

}
