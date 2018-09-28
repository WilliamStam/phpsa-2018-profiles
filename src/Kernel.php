<?php
namespace mostertb\PHPSA2018Profiles;

use mostertb\PHPSA2018Profiles\Profiles\AbstractProfile;
use mostertb\PHPSA2018Profiles\Profiles\BradMostertProfile;
use mostertb\PHPSA2018Profiles\Profiles\WilliamStamProfile;

class Kernel
{
    /**
     * @var AbstractProfile[]
     */
    private $profiles;

    /**
     * Kernel constructor.
     */
    public function __construct()
    {
        $this->profiles = array(
            new BradMostertProfile(),
			new WilliamStamProfile()
        );
    }

    /**
     * @return AbstractProfile[]
     */
    public function getProfiles()
    {
        return $this->profiles;
    }

	/**
	 * USAGE: $kernel->clean($profile->getBiography(),"p,br,strong,em");
	 */

	/**
	 *	Remove HTML tags (except those enumerated) and non-printable
	 *	characters to mitigate XSS/code injection attacks
	 *	@return mixed
	 *	@param $arg mixed
	 *	@param $tags string
	 **/
	function clean($arg,$tags=NULL) {
		return $this->recursive($arg,
			function($val) use($tags) {
				if ($tags!='*')
					$val=trim(strip_tags($val,
						'<'.implode('><',$this->split($tags)).'>'));
				return trim(preg_replace(
					'/[\x00-\x08\x0B\x0C\x0E-\x1F]/','',$val));
			}
		);
	}

	/**
	 *	Invoke callback recursively for all data types
	 *	@return mixed
	 *	@param $arg mixed
	 *	@param $func callback
	 *	@param $stack array
	 **/
	function recursive($arg,$func,$stack=[]) {
		if ($stack) {
			foreach ($stack as $node)
				if ($arg===$node)
					return $arg;
		}
		switch (gettype($arg)) {
			case 'object':
				$ref=new \ReflectionClass($arg);
				if ($ref->iscloneable()) {
					$arg=clone($arg);
					$cast=is_a($arg,'IteratorAggregate')?
						iterator_to_array($arg):get_object_vars($arg);
					foreach ($cast as $key=>$val)
						$arg->$key=$this->recursive(
							$val,$func,array_merge($stack,[$arg]));
				}
				return $arg;
			case 'array':
				$copy=[];
				foreach ($arg as $key=>$val)
					$copy[$key]=$this->recursive($val,$func,
						array_merge($stack,[$arg]));
				return $copy;
		}
		return $func($arg);
	}
	/**
	 *	Split comma-, semi-colon, or pipe-separated string
	 *	@return array
	 *	@param $str string
	 *	@param $noempty bool
	 **/
	function split($str,$noempty=TRUE) {
		return array_map('trim',
			preg_split('/[,;|]/',$str,0,$noempty?PREG_SPLIT_NO_EMPTY:0));
	}
}