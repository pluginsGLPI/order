<?php

use Zend\Loader\SplAutoloader;

class PluginOrderAutoloader implements SplAutoloader
{
   protected $paths = array();

   public function __construct($options = null) {
      if (null !== $options) {
         $this->setOptions($options);
      }
   }

   public function setOptions($options) {
      if (!is_array($options) && !($options instanceof \Traversable)) {
         throw new \InvalidArgumentException();
      }

      foreach ($options as $path) {
         if (!in_array($path, $this->paths)) {
            $this->paths[] = $path;
         }
      }
      return $this;
   }

   public function processClassname($classname) {
      preg_match("/Plugin([A-Z][a-z0-9]+)([A-Z]\w+)/", $classname, $matches);

      if (count($matches) < 3) {
         return false;
      } else {
         return $matches;
      }

   }

   public function autoload($classname) {
      foreach ($this->paths as $path) {
         $file = $path . str_replace('\\', '/', $classname) . '.php';
         if (file_exists($file)) {
            return include($file);
         }
      }

      return false;
   }

   public function register() {
      spl_autoload_register(array($this, 'autoload'));
   }
}

