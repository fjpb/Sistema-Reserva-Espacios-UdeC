<?php
declare(strict_types=1);
namespace MRBS;

use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\QROptions;
use MRBS\Form\ElementInputDate;
use MRBS\Form\ElementInputSearch;
use MRBS\Form\ElementInputSubmit;
use MRBS\Form\Form;

function print_head(bool $simple=false) : void
{
  global $refresh_rate;

  echo "<head>\n";
  echo "<meta charset=\"" . get_charset() . "\">\n";
  echo "<meta http-equiv=\"X-UA-Compatible\" content=\"IE=edge\">\n";
  echo "<meta name=\"viewport\" content=\"width=device-width, initial-scale=1\">\n";

  if (!$simple)
  {
    echo "<meta name=\"csrf_token\" content=\"" . htmlspecialchars(Form::getToken()) . "\">\n";
  }

  echo "<meta name=\"robots\" content=\"noindex, nofollow, noarchive\">\n";

  if (($refresh_rate != 0) && (this_page(false, '.php') == 'index'))
  {
    echo "<noscript>\n";
    echo "<meta http-equiv=\"Refresh\" content=\"$refresh_rate\">\n";
    echo "</noscript>\n";
  }

  echo "<title>" . get_vocab("mrbs") . " - Universidad de Concepción</title>\n";

  // Google Fonts Inter
  echo "<link rel=\"preconnect\" href=\"https://fonts.googleapis.com\">\n";
  echo "<link rel=\"preconnect\" href=\"https://fonts.gstatic.com\" crossorigin>\n";
  echo "<link href=\"https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap\" rel=\"stylesheet\">\n";
  // Font Awesome 6
  echo "<link rel=\"stylesheet\" href=\"https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css\">\n";

  require_once MRBS_ROOT . "/style.inc";

  // Carga e inyección dinámica de colores de tipos de reserva desde el panel visual
  $types_config_file = __DIR__ . '/types_config.json';
  if (is_file($types_config_file))
  {
    $types_dyn = json_decode(file_get_contents($types_config_file), true);
    if (is_array($types_dyn) && !empty($types_dyn))
    {
      echo "<style id=\"udec-types-dynamic-styles\">\n";
      foreach ($types_dyn as $t)
      {
        $c = htmlspecialchars($t['code'] ?? '');
        $col = htmlspecialchars($t['color'] ?? '');
        if ($c !== '' && $col !== '')
        {
          echo ".dwm_main td.$c, .dwm_main div.$c, .dwm_main a.$c, .dwm_main [data-type=\"$c\"], #month_main .$c, .color_key .$c, .udec-hc-badge.$c { background-color: $col !important; }\n";
        }
      }
      echo "</style>\n";
    }
  }

  if (!$simple)
  {
    require_once MRBS_ROOT . "/js.inc";
  }

  echo "</head>\n";
}


// Imprime la marca e identidad institucional UdeC
function print_header_site_info() : void
{
  global $mrbs_company,
         $mrbs_company_url,
         $mrbs_company_logo;

  $logo_path = 'images/escudo-udec.png';
  if (!empty($mrbs_company_logo) && is_file(__DIR__ . '/../../' . $mrbs_company_logo))
  {
    $logo_path = $mrbs_company_logo;
  }

  echo "<div class=\"udec-brand-container\">\n";
  echo "  <div class=\"udec-logo-wrap\">\n";
  echo "    <a href=\"" . htmlspecialchars(multisite('index.php')) . "\">\n";
  echo "      <img src=\"" . htmlspecialchars($logo_path) . "\" alt=\"Escudo UdeC\" class=\"udec-logo-img\">\n";
  echo "    </a>\n";
  echo "  </div>\n";
  echo "  <div class=\"udec-title-wrap\">\n";
  echo "    <span class=\"udec-sub-title\">Universidad de Concepción</span>\n";
  echo "    <div class=\"udec-main-title\">\n";
  echo "      <a href=\"" . htmlspecialchars(multisite('index.php')) . "\">Portal de Reservas</a>\n";
  echo "      <span class=\"udec-badge-version\">Campus Los Ángeles</span>\n";
  echo "    </div>\n";
  echo "  </div>\n";
  echo "</div>\n";
}


function print_goto_date(array $context) : void
{
  global $multisite, $site;

  if (!checkAuthorised('index.php', true))
  {
    return;
  }

  $form = new Form();
  $form_id = 'form_nav';

  $form->setAttributes(array('id'     => $form_id,
                             'class'  => 'js_hidden',
                             'action' => multisite('index.php')))
       ->addHiddenInput('view', $context['view']);

  if (isset($context['area']))
  {
    $form->addHiddenInput('area', $context['area']);
  }

  if (isset($context['room']))
  {
    $form->addHiddenInput('room', $context['room']);
  }

  if ($multisite && isset($site) && ($site !== ''))
  {
    $form->addHiddenInput('site', $site);
  }

  $input = new ElementInputDate();
  $input->setAttributes(array('name'        => 'page_date',
                              'value'       => format_iso_date($context['year'], $context['month'], $context['day']),
                              'aria-label'  => get_vocab('goto'),
                              'required'    => true,
                              'data-submit' => $form_id));

  $form->addElement($input);

  $submit = new ElementInputSubmit();
  $submit->setAttribute('value', get_vocab('goto'));
  $form->addElement($submit);

  $form->render();
}


function print_outstanding(string $query) : void
{
  $mrbs_user = session()->getCurrentUser();

  if (!isset($mrbs_user))
  {
    return;
  }

  $approval_somewhere = some_area('approval_enabled', TRUE);
  if ($approval_somewhere && ($mrbs_user->level > 0))
  {
    $n_outstanding = get_entries_n_outstanding($mrbs_user);

    $class = 'notification';
    if ($n_outstanding > 0)
    {
      $class .= ' attention';
    }

    $url = 'pending.php';
    if ($query !== '')
    {
      $url .= "?$query";
    }
    echo '<a href="' . htmlspecialchars(multisite($url)) . '"' .
         " class=\"$class\"" .
         ' title="' . get_vocab('outstanding', $n_outstanding) .
         "\"><i class=\"fa-regular fa-bell\"></i> $n_outstanding</a>\n";
  }
}


function print_menu_items(string $query) : void
{
  global $auth, $kiosk_mode_enabled;

  $menu_items = array('help'   => array('file' => 'help.php', 'icon' => 'fa-circle-question'),
                      'report' => array('file' => 'report.php', 'icon' => 'fa-chart-pie'),
                      'import' => array('file' => 'import.php', 'icon' => 'fa-file-import'));

  if ($kiosk_mode_enabled)
  {
    $menu_items['kiosk'] = array('file' => 'kiosk.php', 'icon' => 'fa-display');
  }

  $menu_items['rooms'] = array('file' => 'admin.php', 'icon' => 'fa-building');
  $menu_items['admin_pending'] = array('file' => 'admin_pending.php', 'icon' => 'fa-clock-rotate-left');
  $menu_items['admin_types'] = array('file' => 'admin_types.php', 'icon' => 'fa-palette');

  if ($auth['type'] == 'db')
  {
    $menu_items['user_list'] = array('file' => 'edit_users.php', 'icon' => 'fa-users');
  }

  foreach ($menu_items as $token => $details)
  {
    $page = $details['file'];
    $icon = $details['icon'];
    if (checkAuthorised($page, true))
    {
      $url = $page;
      if ($query !== '')
      {
        $url .= "?$query";
      }

      $badge_html = '';
      if ($token === 'admin_pending')
      {
        try
        {
          $today_ts = mktime(0, 0, 0);
          $mask_p = STATUS_AWAITING_APPROVAL | STATUS_TENTATIVE;
          $sql_p = "SELECT COUNT(CASE WHEN E.repeat_id IS NULL OR E.repeat_id = 0 THEN 1 END) +
                           COUNT(DISTINCT CASE WHEN E.repeat_id IS NOT NULL AND E.repeat_id > 0 THEN E.repeat_id END)
                      FROM " . _tbl('entry') . " E
                INNER JOIN " . _tbl('room') . " M ON E.room_id = M.id
                INNER JOIN " . _tbl('area') . " A ON M.area_id = A.id
                     WHERE M.disabled = 0
                       AND A.disabled = 0
                       AND (E.status & $mask_p) != 0
                       AND E.start_time >= ?";
          $n_pend = (int)db()->query1($sql_p, array($today_ts));
          if ($n_pend > 0)
          {
            $badge_html = ' <span class="udec-pending-nav-badge" title="' . $n_pend . ' solicitudes pendientes desde hoy">' . $n_pend . '</span>';
          }
        }
        catch (\Throwable $e)
        {
          $badge_html = '';
        }
      }

      echo '<a href="' . htmlspecialchars(multisite($url)) . '" class="udec-nav-link">' .
           '<i class="fa-solid ' . $icon . '"></i> ' . get_vocab($token) . $badge_html . "</a>\n";
    }
  }
}


function print_search(array $context) : void
{
  if (!checkAuthorised('search.php', true))
  {
    return;
  }

  echo "<div class=\"udec-search-box\">\n";

  $form = new Form(Form::METHOD_POST);
  $form->setAttributes(array(
          'id'     => 'header_search',
          'action' => multisite('search.php'))
        )
       ->addHiddenInputs(array(
          'view'       => $context['view'],
          'year'       => $context['year'],
          'month'      => $context['month'],
          'day'        => $context['day'],
          'from_date'  => format_iso_date($context['year'], $context['month'], $context['day'])
        )
     );

  if (!empty($context['area']))
  {
    $form->addHiddenInput('area', $context['area']);
  }
  if (!empty($context['room']))
  {
    $form->addHiddenInput('room', $context['room']);
  }

  $input = new ElementInputSearch();
  $search_vocab = get_vocab('search');

  $input->setAttributes(array('name'        => 'search_str',
                              'placeholder' => $search_vocab,
                              'aria-label'  => $search_vocab,
                              'required'    => true));

  $form->addElement($input);

  $submit = new ElementInputSubmit();
  $submit->setAttributes(array('value' => get_vocab('search_button'),
                               'class' => 'js_none'));
  $form->addElement($submit);

  $form->render();

  echo "</div>\n";
}


function print_report_link(User $user) : void
{
  if (checkAuthorised('report.php', true))
  {
    $attributes = array('action' => multisite('report.php'));
    $hidden_inputs = array('phase'        => '2',
                           'creatormatch' => $user->username);
  }
  elseif (checkAuthorised('search.php', true))
  {
    $attributes = array('action' => multisite('search.php'));
    $date_now = new DateTime();
    $hidden_inputs = array(
      'search_str' => $user->username,
      'from_date'  => $date_now->getISODate()
    );
  }
  else
  {
    echo '<div class="udec-user-pill"><i class="fa-solid fa-user-circle"></i> ' . htmlspecialchars($user->display_name) . '</div>';
    return;
  }

  $form = new Form(Form::METHOD_POST);
  $attributes['id'] = 'show_my_entries';
  $form->setAttributes($attributes)->addHiddenInputs($hidden_inputs);

  $submit = new ElementInputSubmit();
  $submit->setAttributes(array('title' => get_vocab('show_my_entries'),
                               'value' => '👤 ' . $user->display_name,
                               'class' => 'udec-user-btn'));
  $form->addElement($submit);

  $form->render();
}


function print_logonoff_button(array $params, string $value) : void
{
  $form = new Form($params['method']);
  $form->setAttributes(array('action' => $params['action'], 'class' => 'udec-auth-form'));

  if ($params['method'] == Form::METHOD_GET)
  {
    $query_string = parse_url($params['action'], PHP_URL_QUERY);
    if (isset($query_string))
    {
      parse_str($query_string, $query_parameters);
      $form->addHiddenInputs($query_parameters);
    }
  }

  if (isset($params['hidden_inputs']))
  {
    $form->addHiddenInputs($params['hidden_inputs']);
  }

  $element = new ElementInputSubmit();
  $element->setAttribute('value', $value);
  $element->setAttribute('class', 'udec-logon-btn');
  $form->addElement($element);

  $form->render();
}


function print_logon() : void
{
  if (method_exists(session(), 'getLogonFormParams'))
  {
    $form_params = session()->getLogonFormParams();
    if (isset($form_params))
    {
      print_logonoff_button($form_params, get_vocab('login'));
    }
  }
}


function print_logoff() : void
{
  if (method_exists(session(), 'getLogoffFormParams'))
  {
    $form_params = session()->getLogoffFormParams();
    if (isset($form_params))
    {
      print_logonoff_button($form_params, get_vocab('logoff'));
    }
  }
}


function print_banner(?array $context, $simple=false, $omit_login=false) : void
{
  global $kiosk_QR_code, $auth;

  echo '<header class="banner udec-banner' . (($simple) ? ' simple' : '') . "\">\n";

  $vars = array();
  if (isset($context['view']))
  {
    $vars['view'] = $context['view'];
  }
  if (isset($context['year']) && isset($context['month']) && isset($context['day']))
  {
    $vars['page_date'] = format_iso_date($context['year'], $context['month'], $context['day']);
  }
  if (isset($context['area']))
  {
    $vars['area'] = $context['area'];
  }
  if (isset($context['room']))
  {
    $vars['room'] = $context['room'];
  }

  $query = http_build_query($vars, '', '&');

  print_header_site_info();

  if (!$simple)
  {
    echo "<nav class=\"container udec-nav-container\">\n";

    echo "<nav class=\"udec-goto-wrap\">\n";
    print_goto_date($context);
    print_search($context);
    echo "</nav>\n";

    // Indicador institucional de período académico (en el centro)
    echo "<div class=\"udec-period-badge\"><span class=\"udec-period-dot\"></span> Periodo Académico Activo</div>\n";

    // Menú de navegación, ayuda y sesión de usuario (en el extremo derecho)
    echo "<nav class=\"udec-menu-wrap\">\n";
    echo "<nav class=\"menu udec-menu\">\n";
    print_menu_items($query);
    echo "</nav>\n";

    echo "<nav class=\"logon udec-logon\">\n";
    print_outstanding($query);

    $mrbs_user = session()->getCurrentUser();
    if (isset($mrbs_user) && (!$auth['allow_anonymous_booking'] || ($mrbs_user->username !== '')))
    {
      print_report_link($mrbs_user);
      print_logoff();
    }
    elseif (!$omit_login)
    {
      print_logon();
    }
    echo "</nav>\n";
    echo "</nav>\n"; // .udec-menu-wrap

    if (isset($context['kiosk']) &&
        $kiosk_QR_code &&
        (version_compare(PHP_VERSION, '7.4') >= 0) &&
        defined('MB_CASE_UPPER'))
    {
      $url = multisite(url_base() . "/index.php?$query");
      echo '<nav class="qr" title="' . htmlspecialchars($url) . "\">\n";
      $options = new QROptions([
        'outputType'  => QRCode::OUTPUT_MARKUP_SVG,
        'imageBase64' => false,
      ]);
      $qrcode = new QRCode($options);
      echo $qrcode->render($url);
      echo "</nav>\n";
    }

    echo "</nav>\n"; // .udec-nav-container
  }

  echo "</header>\n";
}


function print_unsupported_message(?array $context) : void
{
  echo "<div class=\"unsupported_message\">\n";
  print_banner($context, true);
  echo "<div class=\"contents\">\n";
  echo "<p>" . get_vocab('browser_not_supported', get_vocab('mrbs_abbr')) . "</p>\n";
  echo "</div>\n";
  echo "</div>\n";
}


function print_theme_header(?array $context=null, bool $simple=false, bool $omit_login=false) : void
{
  global $multisite, $site, $default_view, $default_view_all, $view_week_number, $style_weekends;

  if ($simple)
  {
    $data = array();
    $classes = array();
  }
  else
  {
    if (!isset($context))
    {
      $context = array();
    }

    if (empty($context['area']))
    {
      $context['area'] = get_default_area();
    }

    if (empty($context['room']))
    {
      $context['room'] = get_default_room($context['area']);
    }

    if (!isset($context['view']))
    {
      $context['view'] = (isset($default_view)) ? $default_view : 'day';
    }

    if (!isset($context['view_all']))
    {
      $context['view_all'] = (isset($default_view_all)) ? $default_view_all : true;
    }

    get_area_settings($context['area']);

    if (!isset($context['year']))
    {
      $context['year'] = (int) date('Y');
    }

    if (!isset($context['month']))
    {
      $context['month'] = (int) date('m');
    }

    if (!isset($context['day']))
    {
      $context['day'] = (int) date('d');
    }

    Form::getToken();

    $page = this_page(false, '.php');

    $data = array(
      'view'          => $context['view'],
      'view_all'      => $context['view_all'],
      'area'          => $context['area'],
      'room'          => $context['room'],
      'page'          => $page,
      'page-date'     => format_iso_date($context['year'], $context['month'], $context['day']),
      'is-admin'      => (is_admin()) ? 'true' : 'false',
      'is-book-admin' => (is_book_admin()) ? 'true' : 'false',
      'lang-prefs'    => json_encode(get_lang_preferences())
    );

    if ($multisite && isset($site) && ($site !== ''))
    {
      $data['site'] = $site;
    }

    if (isset($context['kiosk']))
    {
      $data['kiosk'] = $context['kiosk'];
    }

    $mrbs_user = session()->getCurrentUser();
    if (isset($mrbs_user))
    {
      $data['username'] = $mrbs_user->username;
    }

    $timetohighlight = get_form_var('timetohighlight', 'int');
    if (isset($timetohighlight))
    {
      $data['timetohighlight'] = $timetohighlight;
    }

    $classes[] = $page;
    if (isset($mrbs_user))
    {
      $classes[] = 'logged_in';
    }

    if ($view_week_number)
    {
      $classes[] = 'view_week_number';
    }
    if ($style_weekends)
    {
      $classes[] = 'style_weekends';
    }
  }

  $headers = array("Content-Type: text/html; charset=" . get_charset());
  http_headers($headers);

  echo DOCTYPE . "\n";
  echo "<!--[if lte IE 9]>\n";
  echo "<html lang=\"" . htmlspecialchars(get_mrbs_lang()) . "\" class=\"unsupported_browser\">\n";
  echo "<![endif]-->\n";
  echo "<!--[if (!IE)|(gt IE 9)]><!-->\n";
  echo "<html lang=\"" . htmlspecialchars(get_mrbs_lang()) . "\">\n";
  echo "<!--<![endif]-->\n";

  print_head($simple);

  echo '<body class="' . htmlspecialchars(implode(' ', $classes)) . '"';
  foreach ($data as $key => $value)
  {
    if (isset($value))
    {
      echo " data-$key=\"" . htmlspecialchars((string)$value) . '"';
    }
  }
  echo ">\n";

  print_unsupported_message($context);
  print_banner($context, $simple, $omit_login);

  echo "<div class=\"contents udec-contents\">\n";
}
