<?php

namespace Controllers;

class RegionController extends Controller
{
    protected $modelName = \Models\RegionModel::class;
    protected string $title;
    protected string $pageContent = 'region';
    protected array $variables = [];

    public function __construct()
    {
        parent::__construct();
        // Init the array $variables with all articles
        $this->variables = ['regions' => $this->findAllArticles()];
        // Init the $title with $_GET['region']
        $this->title = "Région " . $_GET['region'];
    }

    /**
     * Returns all the articles by region
     * 
     * @return array
     */
    public function findAllArticles(): array
    {

        $region = null;
        try {
            // if $_GET region is not empty, init the variable $region
            if (!empty($_GET['region'])) {
                $region = $_GET['region'];
            } else {
                throw new \ErrorException('URL incorrecte');
            }
            // find all articles by get['region] in database
            $result = $this->model->findAllByItem($region);
            // if result is not empty, returns the result
            if (!empty($result)) {
                return $result;
            } else {
                throw new \ErrorException('URL incorrecte');
            }
        } catch (\ErrorException $e) {
            ErrorController::error404($e);
        }
    }
}
