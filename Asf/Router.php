<?php
/*
 * Asf_Router is used to analyze uri, then get controller and action name.
* althrough it very powerful, but you still need think about the performance.
* just design your application as simple as you can.
* Author: yiyuanjian
*
*/
class Asf_Router {

    private $maps = array();
    private $regex = array();

    private $orders = array();

    /**
     * 
     * @param string $ctlParam
     * @param string $actParam
     * @return Asf_Router
     * @example addSimple('index','index')
     */
    public function addSimple($ctlParam = '__c', $actParam = '__a') {
        if(empty($ctlParam) || empty($actParam)) {
            exit('Call '.__METHOD__.', parameters invalid');
        }

        $this->orders[] = array('type' => 'simple',
                'params' => array('ctl' => $ctlParam,
                        'act' => $actParam)
        );

        return $this;
    }

    /**
     * 
     * @param string $uri
     * @param array $map array('ctl' => ctlName, 'act' => actName)
     * @return Asf_Router
     * @example addMap('/index/json', array('ctl' => 'index', 'act' => 'json')
     */
    public function addMap($uri, $map) {
        if (empty($uri) || !is_array($map) || !isset($map['ctl']) || !isset($map['act'])) {
            exit('Call '.__METHOD__.', parameters invalid');
        }

        $this->orders[] = array('type' => 'map',
                'params' => array('uri' => $uri, 'map' => $map)
        );

        return $this;
    }

    /**
     * 
     * @param string $uri
     * @return Asf_Router
     * @example addRewrite('/:ctl/:act')
     */
    public function addRewrite($uri) {
        if (empty($uri)) {
            exit('Call '.__METHOD__.', parameters invalid');
        }
        $ctlPos = strpos($uri, '/:ctl');
        $actPos = strpos($uri, '/:act');
        $paramsArray = preg_split('/\/:?/', $uri);
        $map = array_flip($paramsArray);

        $uri = str_replace(array('/:ctl','/:act'), '/([a-zA-Z0-9_-]+)', $uri);
        $uri = str_replace('/', '\/', $uri);
        $this->addRegex('/^'.$uri.'/', $map);

        return $this;
    }

    /**
     * 
     * @param string $regex
     * @param array $map
     * @return Asf_Router
     * @example $router->addRegex('/([a-z]+)\/([a-z]+)/', array("ctl" => 1,"act" => 2));
     */
    public function addRegex ($regex, $map = array('ctl' => 1, 'act' => 2)) {
        $this->orders[] = array('type' => 'regex',
                'params' => array('uri' => $regex, 'map' => $map)
        );

        return $this;
    }

    public function init() {
        return $this;
    }

    public function handle() {
        return $this;
    }

    public function getPath() {
        foreach ($this->orders as $order) {
            $path = array('ctl' => '', 'act' => '');
            switch ($order['type']) {
                case 'simple':
                    $path['ctl'] = Asf_Request::getString($order['params']['ctl'],40);
                    $path['act'] = Asf_Request::getString($order['params']['act'],40);
                    break;
                case 'map':
                    if ($_SERVER['REQUEST_URI'] == $order['params']['uri']) {
                        $path['ctl'] = $order['params']['map']['ctl'];
                        $path['act'] = $order['params']['map']['act'];
                    }
                    break;
                case 'regex':
                    if(preg_match($order['params']['uri'], $_SERVER['REQUEST_URI'], $match)) {
                    	$ctlIndex = $order['params']['map']['ctl'];
                    	$actIndex = $order['params']['map']['act'];
                        $path['ctl'] = is_int($ctlIndex) ? $match[$ctlIndex] : $ctlIndex;
                        $path['act'] = is_int($actIndex) ? $match[$actIndex] : $actIndex;
                        foreach ($order['params']['map'] as $k => $v) {
                            if(!in_array($k, array_keys($path))) {
                                $_GET[$k] = $match[$v];
                            }
                        }
                    }
                    break;

                default:
                    ;
                    break;
            }

            if($path['ctl'] ) {
                return $path;
            }
        }
    }
}