<?php

namespace App\Enums;

enum ProjectType: string
{
    case App = 'app';
    case Drone = 'drone';
    case CaseStudy = 'case_study';
}
