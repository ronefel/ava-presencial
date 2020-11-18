<?php
    function eadmaterial_user_candoanything($courseid) {
        $context = context_system::instance(0);

        return (has_capability('moodle/site:doanything', $context));
    }
?>