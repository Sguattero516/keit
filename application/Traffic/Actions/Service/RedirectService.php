<?php
namespace Traffic\Actions\Service;

use Traffic\Service\AbstractService;

class RedirectService extends AbstractService
{

    public function scriptRedirect($url)
    {
        return 'function process() {
                window.location = "' . $url . '";
            }
            window.onerror = process;
            process();
        ';
    }

    public function frameRedirect($url)
    {
        return '<script type="application/javascript">
            function process() {
                top.location = "' . $url . '";
            }

            window.onerror = process;

            if (top.location.href != window.location.href) {
                process()
            }
        </script>';
    }

    public function metaRedirect($url)
    {
        return '<html>
            <head>
                <meta http-equiv="REFRESH" content="1; URL=\'' . $url . '\'">
            </head>
            </html>';
    }
}