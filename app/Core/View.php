<?php
/**
 * Sport Shop – View / Template Renderer
 *
 * Renders PHP template files with extracted data, layout support,
 * and automatic injection of common variables.
 */

namespace App\Core;

class View
{
    /**
     * Render a template file.
     *
     * @param string $template  Dot-notation path relative to Views/ (e.g., "shop.home")
     * @param array  $data      Variables to extract into the template scope.
     */
    public static function render(string $template, array $data = []): void
    {
        $file = self::resolvePath($template);

        if (!file_exists($file)) {
            throw new \RuntimeException("View không tồn tại: {$template} ({$file})");
        }

        // Inject common variables
        $data = array_merge(self::commonData(), $data);

        // Extract data as local variables and include the template
        extract($data, EXTR_SKIP);

        include $file;
    }

    /**
     * Render content within a layout.
     *
     * The layout file should echo $content where the page body goes.
     *
     * @param string $layout   Layout template name (e.g., "layouts.main")
     * @param string $content  Already-rendered HTML content string.
     * @param array  $data     Extra variables for the layout.
     */
    public static function layout(string $layout, string $content, array $data = []): void
    {
        $data['content'] = $content;
        self::render($layout, $data);
    }

    /**
     * Render a template and capture its output as a string.
     *
     * Useful for rendering a page into $content before wrapping with a layout.
     *
     * @param string $template  Template name.
     * @param array  $data      Variables for the template.
     * @return string
     */
    public static function capture(string $template, array $data = []): string
    {
        ob_start();
        self::render($template, $data);
        return ob_get_clean();
    }

    /**
     * Render a page inside the default layout.
     *
     * Convenience: captures the page template, then wraps it in layouts.main.
     *
     * @param string $template  Page template name (e.g., "shop.home")
     * @param array  $data      Variables for the page template.
     * @param string $layout    Layout to use (default "layouts.main").
     */
    public static function page(string $template, array $data = [], string $layout = 'layouts.main'): void
    {
        $content = self::capture($template, $data);
        self::layout($layout, $content, $data);
    }

    /**
     * Get flash messages from the session and clear them.
     */
    public static function getFlash(): array
    {
        $flash = $_SESSION['_flash'] ?? [];
        unset($_SESSION['_flash']);
        return $flash;
    }

    // -------------------------------------------------------------------
    // Internal helpers
    // -------------------------------------------------------------------

    /**
     * Resolve a dot-notation template name to a file path.
     *
     * "shop.home" → VIEW_PATH/shop/home.php
     */
    private static function resolvePath(string $template): string
    {
        $relative = str_replace('.', DIRECTORY_SEPARATOR, $template);
        return VIEW_PATH . DIRECTORY_SEPARATOR . $relative . '.php';
    }

    /**
     * Common variables automatically available in every template.
     */
    private static function commonData(): array
    {
        return [
            'csrf_token'  => CSRF::getToken(),
            'csrf_field'  => CSRF::getFormField(),
            'csrf_meta'   => CSRF::getMetaTag(),
            'auth_user'   => Auth::user(),
            'auth_check'  => Auth::check(),
            'auth_admin'  => Auth::isAdmin(),
            'app_name'    => \App\Config\env('APP_NAME', 'Sport Shop'),
            'app_url'     => \App\Config\env('APP_URL', ''),
            'flash'       => self::getFlash(),
        ];
    }
}
