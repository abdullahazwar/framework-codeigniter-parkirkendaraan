<?php

// XAMPP commonly serves the checkout directory inside htdocs.
// Keep CodeIgniter's front controller and all public assets in public/.
header('Location: public/', true, 302);
exit;
