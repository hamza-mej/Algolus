<?php

namespace App\Data;

use App\Entity\Category;
use App\Entity\Size;

class SearchData
{
    /**
     * @var int
     */
    public $page = 1;

    /**
     * @var string
     */
    public $q = '';

    /**
     * @var Category[]
     */
    public $categories = [];

    /**
     * @var Color[]
     */
    public $color = [];

    /**
     * @var Size[]
     */
    public $size = [];

    /**
     * @var null|integer
     */
    public $max;

    /**
     * @var null|integer
     */
    public $min;

    /**
     * @var boolean
     */
    public bool $onSale = false;
}