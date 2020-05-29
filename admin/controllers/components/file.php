<?
class FileComponent extends Object
{
	var $filename;
	var $allowed_extentions = array();// ��������� ����� , ���� jpg, (JPG - ������)
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
			$this->error = '������ ����� ��������� ����������� ���������� ������, �������� upload_max_filesize � php.ini. ������� �������� ��������� = '.ini_get('upload_max_filesize');
			return false;
		}
		if(!is_uploaded_file($this->file['tmp_name']))
		{
			$this->error = '�������� ����.';
			return false;
		}
		if(!in_array(strtolower($this->file_ext),$this->allowed_extentions))
		{
			$this->error = '�������� ������ ����� ('.strtoupper(implode(',',$this->allowed_extentions)).').';
			return false;
		}	
		return true;
	}
	
	function upload($file, $filename_without_ext, $path)
    {

		if(!$this->validate($file)) return false;
		
		$this->filename = $filename_without_ext.'.'.strtolower($this->file_ext); 
		
		// �������� ���� �� ������ 
		if(!copy($this->file['tmp_name'],$path.$this->filename))
		{
			$this->error = '������ ��� �������� ����� �� ������.';
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
		
		// ���� ������ ����� > 84 pixels ��������� ����� �� ������ 84
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
      ## ���������� �����
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

		// ����� �� �������
		// ���������������
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