<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Youtube API module for Kohana
 * @author wwebfor
 * @url https://github.com/wwebfor/youtube-video
 */
class Kohana_Youtube
{
    /**
     * @var array configs
     */
    protected $configs = NULL;

    /**
     * @var array data parameters
     */
    protected $params = array();

    /**
     * @var string feeds type
     */
    protected $type = 'videos';

    /**
     * @var string request url
     */
    protected $_url = NULL;

    public static function factory($type = 'videos')
    {
        return new Kohana_Youtube($type);
    }

    public function __construct($type)
    {
        $this->type = $type;

        if ($this->configs == NULL)
        {
            $this->configs = Kohana::$config->load('youtube');
        }
    }

    /**
     * Get results
     * 
     * @access public
     * @return result
     */
    public function find_all()
    {
        $result = $this->execute();
        return $result;
    }

    /**
     * Get single result 
     *
     * @access public
     */
    public function find()
    {
        $this->setParam('max-results', 1);
        return $this->execute();
    }

    /**
     * Advanced search
     *
	   * @param string $column (q|author|format|time)
	   * @param string $value 
     * @return $this
     */
    public function where($column = 'q', $value)
    {
		    $value = stripslashes($value);
		    
		    // sanitizing
		    $column = strtolower($column);
		    $value  = urlencode($value);		

        $this->setParam($column, $value);

		    return $this;
    }

    /**
     * Items limits
     * 
     * @param int $limit 
     * @return $this
     */
    public function limit($limit)
    {
        $this->setParam('max-results', intval($limit));
        return $this;
    }

    /**
     * Offset
     * 
     * @param int $offset 
     * @return $this
     */
    public function offset($offset)
    {
        $this->setParam('start-index', intval($offset));
        return $this;
    }

    /**
     * Sorting
     * 
     * @param mixed $column 
     * @return $this
     */
    public function order_by($column)
    {
        $this->setParam('orderby', $column);
        return $this;
    }

    /**
     * Sets parameters for standard Google API
     * 
     * @param string $name 
     * @param string $value 
     * @access public
     * @return $this
     */
    public function setParam($name, $value)
    {
        $this->params[$name] = $value;
        return $this;
    }

    /**
     * Build API URL for this request
     * 
     * @access protected
     * @return void
     */
    protected function build_url()
    {
        $configs = $this->configs;
        $this->_url = $configs['api_url'] . $this->type;

        $this->_url .= '?alt=jsonc';
        $this->_url .= '&v=' . $configs['version'];

        if ($configs['api_key'] != '')
        {
            $this->_url .= '&key=' . $configs['api_key'];
        }

        foreach ($this->params as $key => $val)
        {
            $this->_url .= '&'.$key . '=' . $val;
        }

        return $this;
    }

    /**
     * Request executing
     * 
     * @access protected
     * @return void
     */
    public function execute()
    {
        if ($this->_url == NULL)
        {
            $this->build_url();
        }

        try
        {
            $result = Request::factory($this->_url)
                ->execute()
                ->body();

            $result = json_decode($result);
        }
        catch (Exception $e)
        {
            throw new Kohana_Exception($e->getMessage);
        }

        if (isset($result->data))
        {
            return $result->data;
        }
        else 
        {
            return NULL;
        }
    }
}// END
