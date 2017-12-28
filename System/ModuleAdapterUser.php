<?php

namespace NE\System {

    /**
     * @author Elchin Nagiyev <elchin at nagiyev.net>
     */
    class ModuleAdapterUser extends ModuleAdapter {

        public function parse() {
            if (empty(elch::$queryStr)) {
                $this->module = elch::$config['default_module'];
                elch::$module = $this->module;
                return;
            }
            if (elch::$queryStr[0] == "") {//mainpage
                $this->module = 'NotFound';
                elch::$module = $this->module;
                return;
            }
            foreach (elch::$queryStr as $k => $v) {
                $w .= ' OR menu.key = :queryStr' . $k;
            }
            $sql = "SELECT menu.*, menu_mui.name
                    FROM `menu`
                    JOIN `menu_mui` on menu.id=menu_mui.fk_menu AND menu_mui.fk_lang=:langId
                    WHERE 1=0 " . $w . " 
                        ORDER BY menu.pid, menu.position";
            dpdo::prepare($sql);
            dpdo::bindParam('langId', elch::$langId, DPDO::INT);
            foreach (elch::$queryStr as $k => $v) {
                dpdo::bindParam('queryStr' . $k, $v, DPDO::STRING);
            }
            dpdo::execute();

            $arr = [];
            $arr2 = [];
            while ($row = dpdo::fetch()) {
                $arr2[$row['id']] = $row;
                $arr[$row['pid']][$row['id']] = &$arr2[$row['id']];
            }
            foreach ($arr2 as $k => $v) {//ağacvari struktur yaradılır
                $arr2[$v['pid']]['sub'][$k] = &$arr2[$k];
            }
            if (empty($arr[0])) {//menyuda yoxdur
                $this->module = 'NotFound';
                elch::$module = $this->module;
                return;
            }

            if ($this->parseNode($arr[0], 0) == false) {
                if (empty(elch::$queryStr)) {
                    $this->module = elch::$config['default_module'];
                } else {
                    $this->module = "NotFound";
                }
            }
            if (!array_key_exists($this->module, elch::$config['modules']) || elch::$config['modules'][$this->module][elch::$config['type']] != 1) {
                $this->module = "NotFound";
            }
            elch::$module = $this->module;
        }

        /**
         * Urldə queryStr-ların hansılarının menyuya hansılarının modula aid olduğunu müəyyənləşdirir.<br />
         * @param array $tree menu tree
         * @param integer $level menunun hansı dərinliyində olduğu və ya elch::$queryStr massivinin key-i
         * @return boolean
         * @example http://site.com/az/career/vacancy/25 saytında menuda career-in vacacncy submenusu yoxdursa və<br /> career xəbərlər moduluna qoşulubsa urlin vacancy/25 hissəsi elch::queryStr['vacancy','25'] kimi qeyd edilir və<br /> xəbərlər modulu çağırılır.
         */
        private function parseNode(array $tree, $level) {
            $n = false;
            foreach ($tree as $arr) {
                if ($arr['key'] == elch::$queryStr[$level]) {
                    PATH::setPath($arr['name'], $arr['key'], $arr['status']);
                    if ($arr['sub']) {
                        $n = $this->parseNode($arr['sub'], $level + 1);
                    }
                    if (!$n) {
                        $r = $this->initModule($arr, $level);
                        return $r;
                    } else {
                        return true;
                    }
                }
            }
            return false;
        }

        /**
         * Urldə queryStr-ların modula aid hissəsini elch::$queryStr-a mənimsədib modulu çağırır..<br />
         * @param array $tree menu tree-nin parseNode metodu ilə modula aid olduğu müəyyən edilmiş hissəsi
         * @param integer $level menunun hansı dərinliyindən və ya elch::$queryStr massivinin hansı key-indən sonrakı hissənin modula aid olduğunu müəyyən edir
         * @return boolean
         * @example http://site.com/az/career/vacancy/25 saytında menuda career-in vacacncy submenusu yoxdursa və<br /> career xəbərlər moduluna qoşulubsa urlin vacancy/25 hissəsi elch::queryStr['vacancy','25'] kimi qeyd edilir və<br /> xəbərlər modulu çağırılır.
         */
        private function initModule($tree, $level) {
            elch::$queryStr_left = array_slice(elch::$queryStr, 0, $level + 1);
            elch::$queryStr = array_slice(elch::$queryStr, $level + 1);
            elch::$grow['Menu']['selected'] = $tree;
            if (array_key_exists($tree['type'], elch::$config['modules'])) {
                $this->module = $tree['type'];

                \NE\Modules\Page\Controller\PageController::$meduId = $tree['id'];
                (new \NE\Modules\Page\Controller\PageController())->initContent($tree['id']);

                if ($tree['options'] != '') {
                    $url_2 = explode('/', $tree['options']);
                    $c = count($url_2);
                    for ($i = $c - 1; $i >= 0; $i--) {//elch::$queryStr-in əvvəlinə $arr['options']-dən gələn queryStr-i əlavə edirik
                        array_unshift(elch::$queryStr, $url_2[$i]); //Nəticədə phone/56 url-i məsələn product/phones/view/56 olur
                        ++elch::$DI['innerParamCountInQueryStr']; //gain::url_function()-da queryStr-da nümunəyə uyğun product/phones/view hissəsini nəzərə almaq üçün lazımdır
                    }
                }
                return true;
            } else {
                $this->module = "NotFound";
                return true;
            }
        }

    }

}
