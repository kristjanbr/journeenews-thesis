<?php
enum AuthStatus: string {
    case AUTHENTICATED = 'user';
    case UNAUTHENTICATED = 'guest';
}
?>