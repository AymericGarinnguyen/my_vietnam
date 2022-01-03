<?php

namespace Controllers;

class DishController extends Controller
{
    protected $modelName = \Models\DishModel::class;
    protected string $title = 'gastronomie';
    protected string $pageContent = 'dishes';
    protected array $variables = [];

    public function __construct()
    {
        parent::__construct();
        // pass all the dishes in the variable
        $this->variables = ['dishes' => $this->findAllDishes()];
    }

    /**
     * Returns all dishes
     * 
     * @return array
     */
    public function findAllDishes(): array
    {
        $select =
            [
                'picture',
                'name',
                'description',
                'region'
            ];
            
        return $this->model->findAll($select, 'id');
    }
}
