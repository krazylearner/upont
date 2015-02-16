<?php

namespace KI\UpontBundle\Tests\Services;

use KI\UpontBundle\Tests\WebTestCase;
use KI\UpontBundle\Services\KIImages;

class KIImagesTest extends WebTestCase
{
    protected $container;
    protected $service;
    protected $path;
    
    public function __construct()
    {
        parent::__construct();
        $this->container = static::$kernel->getContainer();
        $this->service = $this->container->get('ki_upont.images');
        $this->path = $this->container->getParameter('upont_images_directory');
    }
    
    public function testUploadBase64()
    {
         $imgResultat=array();
         $imgResultat=$this->service->uploadBase64($this->base64);
         $this->assertTrue($imgResultat['image']!=NULL);
    }
    
	public function testExtUploadBase64()
    {
         $imgResultat=array();
         $resultat=$this->service->uploadBase64($this->base64);
         $this->assertEquals("png",$resultat['extension']) ;  
    }
	
	
    public function testUploadUrl()
    {
        $url = 'http://www.youtube.com/yt/brand/media/image/YouTube-logo-full_color.png';
        $resultat = $this->service->uploadUrl($url);
        $this->assertTrue($resultat['image']!=NULL);
    }
    
	 public function testExtUploadUrl()
    {
        $url = 'http://www.youtube.com/yt/brand/media/image/YouTube-logo-full_color.png';
        $resultat=$this->service->uploadUrl($url);
        $this->assertEquals($resultat['extension'],"png");  
    }
    

    protected $base64 = 'iVBORw0KGgoAAAANSUhEUgAAABwAAAASAQMAAAByySynAAAABlBMVEUAAAD///+l2Z/dAAAAP0lEQVQImWNgPm9gwAAmbM4bH4AQzAdAYiDC/rzxByTi/+f/cIL5AwPnZGYGIGHMwGA5mdkASNgbMNgJ80AIAMCSHqNvm2VtAAAAAElFTkSuQmCC';

}
