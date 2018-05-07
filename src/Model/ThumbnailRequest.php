<?php
namespace GemeenteAmsterdam\ThumbnailService\Model;

use Symfony\Component\Validator\Constraints as Assert;

class ThumbnailRequest
{
    /**
     * @Assert\NotBlank
     * @Assert\Url
     * @Assert\Length(max=2048)
     */
    public $url;
    
    /**
     * @Assert\NotBlank
     * @Assert\Type("int")
     * @Assert\Range(min=16, max=2048)
     */
    public $width;
    
    /**
     * @Assert\NotBlank
     * @Assert\Type("int")
     * @Assert\Range(min=16, max=2048)
     */
    public $height;
    
    /**
     * @Assert\NotBlank
     * @Assert\Choice(choices={"pdf", "doc"})
     */
    public $inputType;
    
    /**
     * @Assert\NotBlank
     * @Assert\Choice(choices={"jpg", "png"})
     */
    public $outputType;
    
    public function __construct($url, $width, $height, $inputType, $outputType)
    {
        $this->url = $url;
        $this->width = $width;
        $this->height = $height;
        $this->inputType = $inputType;
        $this->outputType = $outputType;
    }
}