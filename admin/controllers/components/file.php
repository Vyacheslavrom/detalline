<?
class FileComponent extends Object
{
	var $filename;
	var $allowed_extentions = array();// прописные буквы , напр jpg, (JPG - ошибка)
	var $error;
	
	var $file;
	var $file_ext;
	
	function init($file)
	{
		$this->file = $file;		
		$s=pathinfo($this->file['name']);
		$this->file_ext = @$s["extension"];
	}
	
	function validate($file)
	{
        $this->init($file);
		if($this->file['error']==1)
		{
			$this->error = 'Размер файла превышает максимально допустимый размер, параметр upload_max_filesize в php.ini. Текущее значение параметра = '.ini_get('upload_max_filesize');
			return false;
		}
		if(!is_uploaded_file($this->file['tmp_name']))
		{
			$this->error = 'Выберите файл.';
			return false;
		}
		if(!in_array(strtolower($this->file_ext),$this->allowed_extentions))
		{
			$this->error = 'Неверный формат файла ('.strtoupper(implode(',',$this->allowed_extentions)).').';
			return false;
		}	
		return true;
	}
	
	function upload($file, $filename_without_ext, $path)
    {

		if(!$this->validate($file)) return false;
		
		$this->filename = $filename_without_ext.'.'.strtolower($this->file_ext); 
		
		// копируем файл на сервер 
		if(!copy($this->file['tmp_name'],$path.$this->filename))
		{
			$this->error = 'Ошибка при загрузке файла на сервер.';
			return false;
		}	
		return true;		
    }
	
	function upload_image($file, $filename_without_ext, $path)
    {
		$this->allowed_extentions = array('jpg','png','bmp','jpeg');
		if(!$this->upload($file, $filename_without_ext, $path)) return false;
		return true;		
    }
	
	function upload_image_and_do_small($file, $filename_without_ext, $path, $resize_width) 
	{
		$path = $path.$resize_width.DS;
		if(!$this->upload_image($file, $filename_without_ext, $path)) return false;
		
		// если ширина копии > 84 pixels уменьшаем копию до ширины 84
		$size = GetImageSize($path.$this->filename);
		if($size[0]>$resize_width)
		{
			$this->resize_image(
				$resize_width,
				$this->procent('width',$resize_width,$path.$this->filename),
				$path.$this->filename,
				$path.$this->filename);
		}
		return true;
	}
	
	function delete($filename_with_path)  {
		@unlink($filename_with_path);
	}
	
	function resize_image($width,$height,$source,$destination) {
      ## Расширение файла
      preg_match('/\.\w+$/',$source,$s);
      if(preg_match('/jpg/i',$s[0]))
      {
          $src = ImageCreateFromJpeg($source);
          //$img = ImageCreate($width,$height);
          $img = imagecreatetruecolor($width, $height);
          imagecopyresized($img, $src, 0, 0, 0, 0, $width, $height, imagesx($src),imagesy($src));
          //@unlink($destination);
          imageJPEG($img,"$destination");
      }
      elseif(preg_match('/gif/i',$s[0]))
      {
          $src = imagecreatefromgif($source);
          //$img = ImageCreate($width,$height);
          $img = imagecreatetruecolor($width, $height);
          imagecopyresized($img, $src, 0, 0, 0, 0, $width, $height, imagesx($src),imagesy($src));
          //@unlink($destination);
          imagegif($img,"$destination");
      }
      elseif(preg_match('/png/i',$s[0]))
      {
          $src = imagecreatefrompng($source);
          //$img = ImageCreate($width,$height);
          $img = imagecreatetruecolor($width, $height);
          imagecopyresized($img, $src, 0, 0, 0, 0, $width, $height, imagesx($src),imagesy($src));
          //@unlink($destination);
          imagepng($img,"$destination");
      }
	}

		// автор Ан Дмитрий
		// масштабирование
		function procent($par,$dst,$source)
		{
		    $size = GetImageSize($source);
		    if($par=='width')
		   	{
		        return $size[1]/($size[0]/$dst);
		    }
		    if($par=='height')
		  	{
		        return $size[0]/($size[1]/$dst);
		    }
		}
}