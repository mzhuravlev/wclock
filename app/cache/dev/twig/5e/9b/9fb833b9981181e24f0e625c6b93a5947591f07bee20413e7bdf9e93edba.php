<?php

/* WClockBundle:Default:index.html.twig */
class __TwigTemplate_5e9b9fb833b9981181e24f0e625c6b93a5947591f07bee20413e7bdf9e93edba extends Twig_Template
{
    public function __construct(Twig_Environment $env)
    {
        parent::__construct($env);

        $this->parent = false;

        $this->blocks = array(
        );
    }

    protected function doDisplay(array $context, array $blocks = array())
    {
        // line 1
        echo "<html>
<head>
    ";
        // line 3
        if (isset($context['assetic']['debug']) && $context['assetic']['debug']) {
            // asset "fe3b23b_0"
            $context["asset_url"] = isset($context['assetic']['use_controller']) && $context['assetic']['use_controller'] ? $this->env->getExtension('routing')->getPath("_assetic_fe3b23b_0") : $this->env->getExtension('assets')->getAssetUrl("_controller/js/fe3b23b_part_1_jquery-2.1.1.min_1.js");
            // line 4
            echo "    <script type=\"text/javascript\" src=\"";
            echo twig_escape_filter($this->env, (isset($context["asset_url"]) ? $context["asset_url"] : $this->getContext($context, "asset_url")), "html", null, true);
            echo "\"></script>
    ";
            // asset "fe3b23b_1"
            $context["asset_url"] = isset($context['assetic']['use_controller']) && $context['assetic']['use_controller'] ? $this->env->getExtension('routing')->getPath("_assetic_fe3b23b_1") : $this->env->getExtension('assets')->getAssetUrl("_controller/js/fe3b23b_part_1_script_2.js");
            echo "    <script type=\"text/javascript\" src=\"";
            echo twig_escape_filter($this->env, (isset($context["asset_url"]) ? $context["asset_url"] : $this->getContext($context, "asset_url")), "html", null, true);
            echo "\"></script>
    ";
        } else {
            // asset "fe3b23b"
            $context["asset_url"] = isset($context['assetic']['use_controller']) && $context['assetic']['use_controller'] ? $this->env->getExtension('routing')->getPath("_assetic_fe3b23b") : $this->env->getExtension('assets')->getAssetUrl("_controller/js/fe3b23b.js");
            echo "    <script type=\"text/javascript\" src=\"";
            echo twig_escape_filter($this->env, (isset($context["asset_url"]) ? $context["asset_url"] : $this->getContext($context, "asset_url")), "html", null, true);
            echo "\"></script>
    ";
        }
        unset($context["asset_url"]);
        // line 6
        echo "</head>
<body>
<div><a href=\"";
        // line 8
        echo $this->env->getExtension('routing')->getPath("w_clock_report");
        echo "\">Отчет</a></div>
<div>
    <form>
        <input id=\"uid\" type=\"hidden\" value=\"user1192\" />
        <input class=\"action\" data-action=\"action_work\" type=\"button\" value=\"Начать работу\">
        <input class=\"action\" data-action=\"action_leave\" type=\"button\" value=\"Завершить работу\">
        <input class=\"action\" data-action=\"action_break\" type=\"button\" value=\"Пойти на обед\">
    </form>
</div>
</body>
</html>";
    }

    public function getTemplateName()
    {
        return "WClockBundle:Default:index.html.twig";
    }

    public function isTraitable()
    {
        return false;
    }

    public function getDebugInfo()
    {
        return array (  51 => 8,  47 => 6,  27 => 4,  23 => 3,  19 => 1,);
    }
}
