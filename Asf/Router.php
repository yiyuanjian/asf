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

    /*
     * /index/index => ctlname = index, actname = index
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

    public function addRewrite($uri, $map) {
        if (empty($uri) || !is_array($map) || !isset($map['ctl']) || !isset($map['act'])) {
            exit('Call '.__METHOD__.', parameters invalid');
        }
        $ctlPos = strpos($uri, '/:ctl');
        $actPos = strpos($uri, '/:act');
        $paramsArray = preg_split('/\/:?/', $uri);
        $paramsKeys = array_flip($paramsArray);

        foreach ($map as $k => &$v) {
            $v = $paramsKeys[$v];
        }

        $uri = str_replace(array('/:ctl','/:act'), '/([a-zA-Z0-9_-]+)', $uri);
        $uri = str_replace('/', '\/', $uri);
        $this->addRegex('/^'.$uri.'/', $map);

        return $this;
    }

    public function addRegex ($regex, $map) {
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
                        $path['ctl'] = $match[$order['params']['map']['ctl']];
                        $path['act'] = $match[$order['params']['map']['act']];
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