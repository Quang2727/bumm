<?php
/**
 * Image Component
 *
 */
App::uses('Component', 'Controller');
class CommonComponent  extends Component {

    var $name = 'Common';
    
     /**
     * initialize method
     *
     * @return void
     */
    public function removeSpaceChar($str) {
    
        $str = trim($str);
        $str = preg_replace ( "/(à|á|ạ|ả|ã|â|ầ|ấ|ậ|ẩ|ẫ|ă|ằ|ắ|ặ|ẳ|ẵ)/", 'a', $str );
        $str = preg_replace ( "/(è|é|ẹ|ẻ|ẽ|ê|ề|ế|ệ|ể|ễ)/", 'e', $str );
        $str = preg_replace ( "/(ì|í|ị|ỉ|ĩ)/", 'i', $str );
        $str = preg_replace ( "/(ò|ó|ọ|ỏ|õ|ô|ồ|ố|ộ|ổ|ỗ|ơ|ờ|ớ|ợ|ở|ỡ)/", 'o', $str );
        $str = preg_replace ( "/(ù|ú|ụ|ủ|ũ|ư|ừ|ứ|ự|ử|ữ)/", 'u', $str );
        $str = preg_replace ( "/(ỳ|ý|ỵ|ỷ|ỹ)/", 'y', $str );
        $str = preg_replace ( "/(đ)/", 'd', $str );
        $str = preg_replace ( "/(À|Á|Ạ|Ả|Ã|Â|Ầ|Ấ|Ậ|Ẩ|Ẫ|Ă|Ằ|Ắ|Ặ|Ẳ|Ẵ)/", 'A', $str );
        $str = preg_replace ( "/(È|É|Ẹ|Ẻ|Ẽ|Ê|Ề|Ế|Ệ|Ể|Ễ)/", 'E', $str );
        $str = preg_replace ( "/(Ì|Í|Ị|Ỉ|Ĩ)/", 'I', $str );
        $str = preg_replace ( "/(Ò|Ó|Ọ|Ỏ|Õ|Ô|Ồ|Ố|Ộ|Ổ|Ỗ|Ơ|Ờ|Ớ|Ợ|Ở|Ỡ)/", 'O', $str );
        $str = preg_replace ( "/(Ù|Ú|Ụ|Ủ|Ũ|Ư|Ừ|Ứ|Ự|Ử|Ữ)/", 'U', $str );
        $str = preg_replace ( "/(Ỳ|Ý|Ỵ|Ỷ|Ỹ)/", 'Y', $str );
        $str = preg_replace ( "/(Đ)/", 'D', $str );
        $str = preg_replace ( "/( )/", '-', $str );
        $str = str_replace('----','-',$str);
        $str = str_replace('---','-',$str);
        $str = str_replace('--','-',$str);
        $str = str_replace('_','-',$str);
        $str = preg_replace('/[^a-zA-Z0-9\-.]/','',$str);
        return strtolower ( $str );
    }
    
    function cutString($str,$len,$more='')
	{
		$str = stripcslashes($str);
		if ($str=="" || $str==NULL) return $str;
		if (is_array($str)) return $str;
		$str = trim($str);
		if (strlen($str) <= $len) return $str;
		$str = substr($str,0,$len);
		if ($str != "") 
		{
			if (!substr_count($str," ")) 
			{
				if ($more) $str .= " ...";
				return $str;
			}
			while(strlen($str) && ($str[strlen($str)-1] != " ")) 
			{
				$str = substr($str,0,-1);
			}
			$str = substr($str,0,-1);
			if ($more) $str .= " ...";
		}
		return $str;
	}
        
}
?>