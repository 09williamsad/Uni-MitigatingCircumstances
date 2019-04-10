<?php
#Header nav bar options depending on user group.
function smarty_function_navbar() {
    $userGroup = groupCheck();
    if ($userGroup == 'Tutor') {
        $navbar = '<div class="grid-item"><a class="button" href="/Requests?selectedTutor=Unassigned">Unassigned Requests</a></div>
                <div class="grid-item"><a class="button" href="/Requests?selectedTutor='. getUserEmail() .'">My Requests</a></div>
                <div class="grid-item"><a class="button" href="/newUser">Add new user</a></div>';
    } elseif ($userGroup == 'Student') {
        $navbar = '<div class="grid-item"><a class="button" href="/submitRequest">Submit Request</a></div>
                <div class="grid-item"><a class="button" href="/Requests?status=Open">My Open Requests</a></div>';
    }
    #profile and signout buttons
    $navbar .= '<div class="grid-item"><a class="button" href="/userDetails?id='. getUserEmail(). '">'. getUserNickName() .'</a></div>
                <div class="grid-item"><a class="button" href="'. getLogoutURL() .'">Log Out</a></div>';
    return $navbar;
}