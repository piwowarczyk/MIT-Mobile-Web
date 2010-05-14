<?

/**
 * Zend Framework
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://framework.zend.com/license/new-bsd
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@zend.com so we can send you a copy immediately.
 *
 * @category   Zend
 * @package    Zend_Gdata
 * @subpackage Gbase
 * @copyright  Copyright (c) 2005-2008 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */

/**
 * @see Zend_Gdata_Query
 */
require_once('Zend/Gdata/Query.php');

/**
 * @see Zend_Gdata_Gbase_Query
 */
require_once('Zend/Gdata/Gbase/Query.php');


/**
 * Assists in constructing queries for Google Base Customer Items Feed
 *
 * @link http://code.google.com/apis/base/
 *
 * @category   Zend
 * @package    Zend_Gdata
 * @subpackage Gbase
 * @copyright  Copyright (c) 2005-2008 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Gdata_Gbase_ItemQuery extends Zend_Gdata_Gbase_Query
{
    /**
     * Path to the customer items feeds on the Google Base server.
     */
    const GBASE_ITEM_FEED_URI = 'http://www.google.com/base/feeds/items';

    /**
     * The default URI for POST methods
     *
     * @var string
     */    
    protected $_defaultFeedUri = self::GBASE_ITEM_FEED_URI;

    /**
     * The id of an item
     *
     * @var string
     */    
    protected $_id = null;

    /**
     * @param string $value
     * @return Zend_Gdata_Gbase_ItemQuery Provides a fluent interface
     */
    public function setId($value)
    {
        $this->_id = $value;
        return $this;
    }

    /*
     * @return string id
     */
    public function getId()
    {
        return $this->_id;
    }

    /**
     * Returns the query URL generated by this query instance.
     * 
     * @return string The query URL for this instance.
     */
    public function getQueryUrl()
    {
        $uri = $this->_defaultFeedUri;
        if ($this->getId() !== null) {
            $uri .= '/' . $this->getId();
        } else {
            $uri .= $this->getQueryString();
        }
        return $uri;
    }

}
