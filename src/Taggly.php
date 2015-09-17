<?php namespace Watson\Taggly;

class Taggly {

   /**
   * The tags for the cloud.
   *
   * @var array
   */
   protected $tags;

   /**
   * The minimum tag count.
   *
   * @var int
   */
   protected $minimumCount;

   /**
   * The maximum tag count.
   *
   * @var int
   */
   protected $maximumCount;

   /**
   * The minimum font size.
   *
   * @var int
   */
   protected $minimumFontSize = 12;

   /**
   * The maximum font size.
   *
   * @var int
   */
   protected $maximumFontSize = 24;

   /**
   * font unit.
   *
   * @var int
   */
   protected $fontUnit = 'px';

   /**
   * The maximum font size.
   *
   * @var int
   */
   protected $addSpace = false;

   /**
   * Whether to shuffle the tags.
   *
   * @var bool
   */
   protected $shuffleTags = true;


   public function __construct(){

      /**
      * Add Laravel 5 Config
      */

      if((int)config('taggly.fontSize.max')){
         $this->setMaximumFontSize((int)config('taggly.fontSize.max'));
      }

      if((int)config('taggly.fontSize.min')){
         $this->setMinimumFontSize((int)config('taggly.fontSize.min'));
      }

      if((string)config('taggly.fontUnit')){
         $this->setFontUnit((string)config('taggly.fontUnit'));
      }

      if(config('taggly.addSpaces') === false || config('taggly.addSpaces') === true){
         $this->setAddSpace((bool)config('taggly.addSpaces'));
      }

      if(config('taggly.shuffleTags') === false || config('taggly.shuffleTags') === true){
         $this->setShuffleTags((bool)config('taggly.shuffleTags'));
      }
   }

   /**
   * Get the tags.
   *
   * @return array
   */
   public function getTags()
   {
      return is_array($this->tags) ? $this->tags : [];
   }

   public function setTags(array $tags = [])
   {
      $this->tags = [];

      foreach ($tags as $tag)
      {
         $this->tags[] = $tag instanceof Tag ? $tag : new Tag($tag);
      }
   }

   /**
   * set font unit.
   *
   * @return string
   */
   public function getFontUnit()
   {
      return $this->fontUnit;
   }

   public function setFontUnit($fontUnit)
   {
      $this->fontUnit = $fontUnit;
      return $this->fontUnit;
   }

   /**
   * Get the lowest count of the tags.
   *
   * @return int
   */
   public function getMinimumCount()
   {
      $counts = array_map  (
         function ($tag){
            return $tag->getCount();
         },
         $tags

      );

      $minCount = min($counts);

      return $minCount;
   }

   /**
   * Get the lowest count of the tags.
   *
   * @return bool
   */
   public function getAddSpace()
   {
      return $this->addSpace;
   }

   /**
   * Get the lowest count of the tags.
   *
   * @return bool
   */
   public function setAddSpace($addSpace)
   {
      $this->addSpace = (bool) $addSpace;
      return $this->addSpace;
   }

   /**
   * Get the highest count of the tags.
   *
   * @return int
   */
   public function getMaximumCount()
   {
      $counts = array_map  (
      function ($tag){
         return $tag->getCount();
      },
      $this->getTags()
   );

   $maxCount = max($counts);

   return $maxCount;
   }

   /**
   * Get the sum count of all tags.
   *
   * @return int
   */
   public function getSumCount()
   {
      $counts = array_map  (
      function ($tag){
         return $tag->getCount();
      },
      $this->getTags()
   );

   return array_sum($counts);
   }

   /**
   * Get the offset between the highest and lowest tag count.
   *
   * @return int
   */
   public function getOffset()
   {
      $offset = $this->getMaximumCount() - $this->getMinimumCount();

      return ($offset < 1) ? 1 : $offset;
   }

   /**
   * Get the minimum font size.
   *
   * @return int
   */
   public function getMinimumFontSize()
   {
      return $this->minimumFontSize;
   }

   /**
   * Set the minimum font size.
   *
   * @param  int  $value
   * @return void
   */
   public function setMinimumFontSize($value)
   {
      $this->minimumFontSize = (int) $value;
   }

   /**
   * Get the maximum font size.
   *
   * @return int
   */
   public function getMaximumFontSize()
   {
      return $this->maximumFontSize;
   }

   /**
   * Set the maximum font size.
   *
   * @param  int  $value
   * @return void
   */
   public function setMaximumFontSize($value)
   {
      $this->maximumFontSize = (int) $value;
   }

   /**
   * Get whether the tags are being shuffled.
   *
   * @return bool
   */
   public function getShuffleTags()
   {
      return $this->shuffleTags;
   }

   /**
   * Set whether the tags are being shuffled.
   *
   * @param  bool  $value
   * @return void
   */
   public function setShuffleTags($value)
   {
      $this->shuffleTags = (bool) $value;
   }

   /**
   * Generate a tag cloud using either the tags provided or tags
   * that have already been registered.
   *
   * @param  array  $tags
   * @return string
   */
   public function cloud(array $tags = null)
   {
      if ($tags) $this->setTags($tags);

      $tags = $this->getTags() ?: [];

      $output = '';

      if ($this->getShuffleTags()) shuffle($tags);

      foreach ($tags as $tag)
      {
         $output .= $this->getTagElement($tag);
      }

      return '<div class="tags">'.$output.'</div>';
   }

   /**
   * Get the font size in units for a given tag.
   *
   * @param  Tag  $tag
   * @return int
   */
   public function getFontSize(Tag $tag)
   {
      $fontSize = ($tag->getCount() / $this->getMaximumCount()) * ($this->getMaximumFontSize() - $this->getMinimumFontSize()) + $this->getMinimumFontSize();
      return $this->getFontUnit() == 'px' ? floor($fontSize) : round($fontSize, 2);
   }

   /**
   * Get the element for a given tag.
   *
   * @param  Tag  $tag
   * @return string
   */
   public function getTagElement(Tag $tag)
   {
      $fontSize = $this->getFontSize($tag);

      $tagString = '';
      if ($tag->getUrl())
      {
         $tagString = '<a href="'.$tag->getUrl().'" class="tag" title="'.$tag->getTag().'" '
         .'style="font-size: '.$fontSize.$this->getFontUnit().'">'.e($tag->getTag()).'</a>';
      }else{
         $tagString = '<span class="tag" title="'.$tag->getTag().'" style="font-size: '
         .$fontSize.$this->getFontUnit().'">'.e($tag->getTag()).'</span>';
      }

      if($this->getAddSpace()){
         $tagString .= ' ';
      }

      return $tagString;

   }

}
