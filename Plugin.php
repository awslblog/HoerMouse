<?php

/**
 * 炫彩鼠标
 * @package HoerMouse
 * @author Hoe
 * @version 1.3.0
 * @link http://www.hoehub.com
 * version 1.0.0 文字气泡
 * version 1.1.0 新增爱心气泡
 * version 1.2.0 新增个性鼠标
 * version 1.3.0 新增fireworks+anime喷墨气泡
 */
class HoerMouse_Plugin implements Typecho_Plugin_Interface
{
    const STATIC_DIR = '/usr/plugins/HoerMouse/static';

    /**
     * 激活插件方法,如果激活失败,直接抛出异常
     *
     * @access public
     * @return void
     */
    public static function activate()
    {
        Typecho_Plugin::factory('Widget_Archive')->footer = array(__CLASS__, 'footer');
    }

    /**
     * 禁用插件方法,如果禁用失败,直接抛出异常
     *
     * @static
     * @access public
     * @return void
     */
    public static function deactivate()
    {
    }

    /**
     * 获取插件配置面板
     *
     * @access public
     * @param Typecho_Widget_Helper_Form $form 配置面板
     * @return void
     */
    public static function config(Typecho_Widget_Helper_Form $form)
    {
        $jquery = new Typecho_Widget_Helper_Form_Element_Radio('jquery',
            ['0' => _t('不加载'), '1' => _t('加载')],
            '1', _t('是否加载外部jQuery库'), _t('插件需要jQuery库文件的支持，如果已加载就不需要加载了 jquery源是新浪Public Resources on SAE：https://lib.sinaapp.com/js/jquery/1.9.1/jquery-1.9.1.min.js'));
        $form->addInput($jquery);

        $layout = new Typecho_Widget_Helper_Layout();
        $layout->html(_t('<h3>气泡类型:</h3><hr>'));
        $form->addItem($layout);

        // 气泡类型
        $options = [
            'none' => _t('无'),
            'text' => _t('文字气泡'),
            'heart' => _t('爱心气泡'),
            'fireworks' => _t('fireworks+anime喷墨气泡'),
        ];
        $bubbleType = new Typecho_Widget_Helper_Form_Element_Radio('bubbleType', $options, 'text', _t('选择气泡类型'));
        $form->addInput($bubbleType);

        // 气泡文字
        $bubbleText = new Typecho_Widget_Helper_Form_Element_Text('bubbleText', null, _t('欢迎来到我的小站!'), _t('请填写文字'), _t('如果选择文字气泡类型, 请填写文字'));
        $form->addInput($bubbleText);

        // 气泡颜色
        $bubbleColor = new Typecho_Widget_Helper_Form_Element_Text('bubbleColor', null, _t('随机'), _t('请填写气泡颜色'), _t('如果选择文字气泡类型, 请填写气泡颜色, 可填入"随机"或十六进制颜色值 如#2db4d8'));
        $form->addInput($bubbleColor);

        // 气泡速度
        $bubbleSpeed = new Typecho_Widget_Helper_Form_Element_Text('bubbleSpeed', null, _t('3000'), _t('请填写气泡速度'), _t('如果选择文字气泡类型, 请填写气泡速度 默认3秒'));
        $form->addInput($bubbleSpeed);

        $layout = new Typecho_Widget_Helper_Layout();
        $layout->html(_t('<h3>鼠标类型:</h3><hr>'));
        $form->addItem($layout);

        $dir = self::STATIC_DIR . '/image';
        // 鼠标样式
        $options = [
            'none' => _t('系统默认'),
            'dew' => "<img src='{$dir}/dew/normal.cur'><img src='{$dir}/dew/link.cur'>",
            'carrot' => "<img src='{$dir}/carrot/normal.cur'><img src='{$dir}/carrot/link.cur'>",
            'exquisite' => "<img src='{$dir}/exquisite/normal.cur'><img src='{$dir}/exquisite/link.cur'>",
            'marisa' => "<img src='{$dir}/marisa/normal.cur'><img src='{$dir}/marisa/link.cur'>",
            'shark' => "<img src='{$dir}/shark/normal.cur'><img src='{$dir}/shark/link.cur'>",
            'sketch' => "<img src='{$dir}/sketch/normal.cur'><img src='{$dir}/sketch/link.cur'>",
            'star' => "<img src='{$dir}/star/normal.cur'><img src='{$dir}/star/link.cur'>",
        ];
        $bubbleType = new Typecho_Widget_Helper_Form_Element_Radio('mouseType', $options, 'dew', _t('选择鼠标样式'));
        $form->addInput($bubbleType);
    }

    /**
     * 个人用户的配置面板
     *
     * @access public
     * @param Typecho_Widget_Helper_Form $form
     * @return void
     */
    public static function personalConfig(Typecho_Widget_Helper_Form $form)
    {
    }

    /**
     *为footer添加js文件
     * @return void
     * @throws Typecho_Plugin_Exception
     */
    public static function footer()
    {
        $HoerMouse = Helper::options()->plugin('HoerMouse');
        $jquery = $HoerMouse->jquery;
        if ($jquery) {
            echo '<script type="text/javascript" src="//lib.sinaapp.com/js/jquery/1.9.1/jquery-1.9.1.min.js"></script>';
        }
        $arr = self::handleBubbleType($HoerMouse);
        echo $arr['html'];
        echo $arr['js'];
        echo "<script>console.log('%c炫彩鼠标插件%chttps://gitee.com/HoeXhe/HoerMouse Hoe主页www.hoehub.com ','line-height:28px;padding:4px;background:#3f51b5;color:#fff;font-size:14px;','padding:4px; color:#673ab7');</script>"; // 你能留下我的信息, 我会很高兴的 ^_^
    }

    /**
     * @param $HoerMouse
     * @return array
     */
    private static function handleBubbleType($HoerMouse)
    {
        $bubbleType = $HoerMouse->bubbleType;
        $dir  = self::STATIC_DIR;
        $js   = '';
        $html = '';
        switch ($bubbleType) {
            case 'text':
                $bubbleColor = $HoerMouse->bubbleColor;
                $bubbleSpeed = (int)$HoerMouse->bubbleSpeed;
                $bubbleText  = $HoerMouse->bubbleText;
                $js .= '<script>';
                $js .= <<<JS
var index = 0;
jQuery(document).ready(function() {
    $(window).click(function(e) {
        var string = "{$bubbleText}";
        var strings = string.split('');
        var span = $("<span>").text(strings[index]);
        index = (index + 1) % strings.length;
        var x = e.pageX,
        y = e.pageY;
        var color = "{$bubbleColor}";
        if (color == "随机") {
            var colorValue="0,1,2,3,4,5,6,7,8,9,a,b,c,d,e,f";
            var colorArray = colorValue.split(",");
            color="#";
            for(var i=0;i<6;i++){
                color+=colorArray[Math.floor(Math.random()*16)];
            }
        }
        span.css({
            "z-index": 999,
            "top": y - 20,
            "left": x,
            "position": "absolute",
            "font-weight": "bold",
            "color": color
        });
        $("body").append(span);
        var styles = {
            "top": y - 160,
            "opacity": 0
        };
        span.animate(styles, {$bubbleSpeed}, function() {
            span.remove();
        });
    });
});
JS;
                $js .= '</script>';
                break;
            case 'heart':
                $js .= '<script>';
                $js .= <<<JS
    // 鼠标点击爱心特效
    !function (e, t, a) {
        function r() {
            for (var e = 0; e < s.length; e++) {
                s[e].alpha <= 0 ? (t.body.removeChild(s[e].el), s.splice(e, 1)) : (s[e].y--, s[e].scale += .004, s[e].alpha -= .013, s[e].el.style.cssText = "left:" + s[e].x + "px;top:" + s[e].y + "px;opacity:" + s[e].alpha + ";transform:scale(" + s[e].scale + "," + s[e].scale + ") rotate(45deg);background:" + s[e].color + ";z-index:99999");
            }
            requestAnimationFrame(r)
        }

        function n() {
            var t = "function" == typeof e.onclick && e.onclick;
            e.onclick = function (e) {
                t && t(),
                    o(e)
            }
        }

        function o(e) {
            var a = t.createElement("div");
            a.className = "heart",
                s.push({
                    el: a,
                    x: e.clientX - 5,
                    y: e.clientY - 5,
                    scale: 1,
                    alpha: 1,
                    color: c()
                }),
                t.body.appendChild(a)
        }

        function i(e) {
            var a = t.createElement("style");
            a.type = "text/css";
            try {
                a.appendChild(t.createTextNode(e))
            } catch (t) {
                a.styleSheet.cssText = e
            }
            t.getElementsByTagName("head")[0].appendChild(a)
        }

        function c() {
            return "rgb(" + ~~(255 * Math.random()) + "," + ~~(255 * Math.random()) + "," + ~~(255 * Math.random()) + ")"
        }

        var s = [];
        e.requestAnimationFrame = e.requestAnimationFrame || e.webkitRequestAnimationFrame || e.mozRequestAnimationFrame || e.oRequestAnimationFrame || e.msRequestAnimationFrame ||
            function (e) {
                setTimeout(e, 1e3 / 60)
            },
            i(".heart{width: 10px;height: 10px;position: fixed;background: #f00;transform: rotate(45deg);-webkit-transform: rotate(45deg);-moz-transform: rotate(45deg);}.heart:after,.heart:before{content: '';width: inherit;height: inherit;background: inherit;border-radius: 50%;-webkit-border-radius: 50%;-moz-border-radius: 50%;position: fixed;}.heart:after{top: -5px;}.heart:before{left: -5px;}"),
            n(),
            r()
    }(window, document);
JS;
                $js .= '</script>';
                break;
            case 'fireworks':
                $html .= '<canvas id="fireworks" style="position:fixed;left:0;top:0;pointer-events:none;"></canvas>';
                $js   .= '<script type="text/javascript" src="https://cdn.bootcss.com/animejs/2.2.0/anime.min.js"></script>';
                $js   .= "<script type='text/javascript' src='{$dir}/js/fireworks.js'></script>";
                break;
        }
        $mouseType = $HoerMouse->mouseType;
        $imageDir  = self::STATIC_DIR . '/image';
        if ($mouseType != 'none') {
            $js .= '<script>';
            $js .= <<<JS
$("body").css("cursor", "url('{$imageDir}/{$mouseType}/normal.cur'), default");
$("a").css("cursor", "url('{$imageDir}/{$mouseType}/link.cur'), pointer");
JS;
            $js .= '</script>';
        }
        return compact('js', 'html');
    }
}
