<?php
/**
* This file is part of the League.url library
*
* @license http://opensource.org/licenses/MIT
* @link https://github.com/thephpleague/url/
* @version 4.0.0
* @package League.url
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/
namespace League\Url;

use Countable;
use IteratorAggregate;
use League\Url\Interfaces\PathInterface;

/**
 *  A class to manipulate URL Path component
 *
 *  @package League.url
 *  @since  1.0.0
 */
class Path extends AbstractSegment implements
    Countable,
    IteratorAggregate,
    PathInterface
{
    /**
     * {@inheritdoc}
     */
    protected $delimiter = '/';

    /**
     * {@inheritdoc}
     */
    public function get()
    {
        $res = [];
        foreach (array_values($this->data) as $value) {
            $res[] = rawurlencode($value);
        }
        if (! $res) {
            return null;
        }

        return implode($this->delimiter, $res);
    }

    /**
     * {@inheritdoc}
     */
    public function getUriComponent()
    {
        return '/'.$this->__toString();
    }

    /**
     * {@inheritdoc}
     */
    public function __toString()
    {
        return (string) $this->get();
    }

    /**
     * {@inheritdoc}
     */
    public function relativeTo(PathInterface $reference = null)
    {
        if (is_null($reference)) {
            return clone $this;
        } elseif ($this->sameValueAs($reference)) {
            return new static;
        }

        $ref_path = array_values($reference->toArray());
        $this_path = array_values($this->data);
        $filename = array_pop($this_path);

        //retrieve the final consecutive identical segment in the current path
        $index = 0;
        foreach ($ref_path as $offset => $value) {
            if (! isset($this_path[$offset]) || $value != $this_path[$offset]) {
                break;
            }
            $index++;
        }
        //deduce the number of similar segment according to the reference path
        $nb_common_segment = count($ref_path) - $index;

        //let's output the relative path using a new Path object
        return new Path(array_merge(
            array_fill(0, $nb_common_segment, '..'),
            array_slice($this_path, $index),
            [$filename]
        ));
    }

    /**
     * {@inheritdoc}
     */
    protected function validate($data)
    {
        $data = $this->sanitizeValue($this->validateSegment($data));

        return array_map('urldecode', $data);
    }

    /**
     * {@inheritdoc}
     */
    protected function formatRemoveSegment($data)
    {
        return array_map('urldecode', parent::formatRemoveSegment($data));
    }
}
