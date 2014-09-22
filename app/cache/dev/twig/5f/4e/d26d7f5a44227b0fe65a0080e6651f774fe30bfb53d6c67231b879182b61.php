<?php

/* WClockBundle:Ajax:ajax.json.twig */
class __TwigTemplate_5f4ed26d7f5a44227b0fe65a0080e6651f774fe30bfb53d6c67231b879182b61 extends Twig_Template
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
        echo twig_jsonencode_filter((isset($context["data"]) ? $context["data"] : $this->getContext($context, "data")));
    }

    public function getTemplateName()
    {
        return "WClockBundle:Ajax:ajax.json.twig";
    }

    public function isTraitable()
    {
        return false;
    }

    public function getDebugInfo()
    {
        return array (  19 => 1,);
    }
}
