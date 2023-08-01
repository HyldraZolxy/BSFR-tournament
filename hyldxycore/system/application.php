<?php
namespace hyldxycore\system;

use Exception;
use hyldxycore\system\backSystem\router\Router;

class Application {
    static private ?Application $_instance = null;
           private Router       $_router;

    /**
     * @throws Exception
     */
    public function __construct() {
        $this->_router = new Router($_GET["args"]);

       /**************
        Add Routes
        **************/
        /** home */
        $this->_router->addRoute(
            "/",
            "hyldxycore\\system\\frontSystem\\HomeController@index"
        );

        /** Tournament */
        $this->_router->addRoute(
            "/tournament/:id",
            "extensions\\tournament\\TournamentController@tournament"
        )->with("id", "[0-9]+");
        $this->_router->addRoute(
            "/tournaments",
            "extensions\\tournament\\TournamentController@tournaments"
        );

        /** Pool */
        $this->_router->addRoute(
            "/tournament/:tournamentID/pool/:poolID",
            "extensions\\pool\\PoolController@pool"
        )->with("tournamentID", "[0-9]+")->with("poolID", "[0-9]+");

        /** Tools */
        $this->_router->addRoute(
            "/tools",
            "extensions\\tools\\ToolsController@index"
        );

        /** Grabber */
        $this->_router->addRoute(
            "/grabber",
            "extensions\\grabber\\GrabberController@index"
        );

        /** Login */
        $this->_router->addRoute(
            "/login",
            "extensions\\login\\LoginController@login"
        );
        $this->_router->addRoute(
            "/logout",
            "extensions\\login\\LoginController@endSession"
        );

        /** API */
        $this->_router->addRoute(
            "/api/getMaps/:tournamentID/:poolID",
            "extensions\\api\\ApiController@getPoolMaps"
        )->with("tournamentID", "[0-9]+")->with("poolID", "[0-9]+");
        $this->_router->addRoute(
            "/api/setScore/",
            "extensions\\api\\ApiController@setScore"
        );

        /** Cron */
        $this->_router->addRoute(
            "/cron",
            "extensions\\cron\\CronController@index"
        );

        /*********************
         Add Errors Routes
         *********************/
        $this->_router->addErrorRoute(
            "/",
            "hyldxycore\\system\\frontSystem\\ErrorController@notFound"
        );
        $this->_router->addErrorRoute(
            "/api",
            "extensions\\api\\ApiController@error"
        );

        echo $this->_router->dispatch();
    }

    static public function getInstance(): ?Application {
        if (is_null(self::$_instance)) self::$_instance = new self();

        return self::$_instance;
    }
}