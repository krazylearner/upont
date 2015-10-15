<?php

namespace KI\PublicationBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as JMS;
use Symfony\Component\Validator\Constraints as Assert;

use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * @ORM\Entity
 * @ORM\HasLifecycleCallbacks
 * @JMS\ExclusionPolicy("all")
 */
class PostFile
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\Column(name="ext", type="string")
     * @Assert\Type("string")
     */
    protected $ext;

    /**
     * @ORM\Column(name="name", type="string", nullable=true)
     * @Assert\Type("string")
     */
    protected $name;

    /**
     * @var integer
     *
     * @ORM\Column(name="size", type="integer")
     */
    private $size;

    /**
     * @Assert\File(maxSize="6000000")
     */
    protected $file;

    /**
     * @ORM\ManyToOne(targetEntity="KI\PublicationBundle\Entity\Post", inversedBy="files")
     * @ORM\JoinColumn(name="post_id", referencedColumnName="id")
     **/
    private $post;

    /**
     * @return string
     */
    protected function getUploadDir()
    {
        return __DIR__.'/../../../../web/uploads/'.$this->getUploadCategory().'/';
    }

    /**
     * @return string
     */
    protected function getUploadCategory()
    {
        return 'posts';
    }


    public function __construct(UploadedFile $uploadedFile, $postId)
    {
        $this->setSize($uploadedFile->getClientSize());
        $this->setExt($uploadedFile->guessExtension());
        $this->setName($postId."_".$uploadedFile->getClientOriginalName()); //SECURITY ISSUE

        $uploadedFile->move($this->getUploadDir(), $this->getName());
    }

    public function getAbsolutePath()
    {
        return $this->getUploadDir().$this->getName();
    }

    public function getWebPath()
    {
        return 'uploads/'.$this->getUploadCategory().'/'.$this->getName();
    }

    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set ext
     *
     * @param string $ext
     * @return PostFile
     */
    public function setExt($ext)
    {
        $this->ext = $ext;

        return $this;
    }

    /**
     * Get ext
     *
     * @return string
     */
    public function getExt()
    {
        return $this->ext;
    }

    /**
     * Set name
     *
     * @param string $name
     * @return PostFile
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set size
     *
     * @param integer $size
     * @return PostFile
     */
    public function setSize($size)
    {
        $this->size = $size;

        return $this;
    }
    /**
     * Get size
     *
     * @return integer
     */
    public function getSize()
    {
        return $this->size;
    }

    /**
     * @return mixed
     */
    public function getFile()
    {
        return $this->file;
    }
    /**
     * @param mixed $file
     */
    public function setFile($file)
    {
        $this->file = $file;
    }

    /**
     * @ORM\PostRemove()
     */
    public function removeFile()
    {
        if ($file = $this->getAbsolutePath())
        {
            unlink($file);
        }
    }

    /**
     * @return mixed
     */
    public function getPost()
    {
        return $this->post;
    }

    /**
     * @param mixed $post
     */
    public function setPost($post)
    {
        $this->post = $post;
    }
}