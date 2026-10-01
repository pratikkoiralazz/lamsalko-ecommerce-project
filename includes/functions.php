<?php
function format_price($amount) {
    return 'NPR ' . number_format($amount, 2);
}