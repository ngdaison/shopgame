<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta name="viewport" content="width=device-width, initial-scale=1.0" />
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<meta name="color-scheme" content="light dark" />
<meta name="supported-color-schemes" content="light dark" />
<title>{{ getAppTitleWithFallback() }}</title>
<style rel="stylesheet" media="all">
/* Base ------------------------------ */

@import url("https://fonts.googleapis.com/css?family=Eigen");

* {
  margin: 0;
  padding: 0;
  width: 100%;
}

*,
*::before,
*::after {
  box-sizing: border-box;
}

body {
  -moz-osx-font-smoothing: grayscale;
  -webkit-font-smoothing: antialiased;
  font-family: "Eigen", Menlo, Monaco, monospace;
  font-size: 16px;
  mso-line-height-rule: exactly;
  line-height: 1.4;
  -ms-text-size-adjust: 100%;
  -webkit-text-size-adjust: 100%;
}

a {
  color: #3869d4;
}

/* Layout ------------------------------ */

.email-wrapper {
  width: 100%;
  margin: 0;
  padding: 0;
  background-color: #f2f4f6;
}

.email-content {
  width: 100%;
  margin: 0;
  padding: 0;
}

/* Masthead ---------------------- */

.email-masthead {
  padding: 25px 0;
  text-align: center;
}

.email-masthead_logo {
  width: 100%;
  max-width: 400px;
  height: auto;
  margin: 0 auto;
  font-size: 16px;
  font-weight: bold;
  color: #2d3748;
  text-decoration: none;
  text-transform: capitalize;
}

.email-masthead_name {
  font-size: 16px;
  font-weight: bold;
  color: #2d3748;
}

/* Body ------------------------------ */

.email-body {
  width: 100%;
  margin: 0;
  padding: 0;
  border-top: 1px solid #edeff2;
  border-bottom: 1px solid #edeff2;
  background-color: #ffffff;
}

.email-body_inner {
  width: 100%;
  max-width: 584px;
  margin: 0 auto;
  padding: 0;
}

.email-footer {
  width: 100%;
  max-width: 584px;
  margin: 0 auto;
  padding: 0;
  -premailer-width: 584px;
  -premailer-cellpadding: 0;
  -premailer-cellspacing: 0;
  text-align: center;
  font-size: 12px;
  color: #9ca3af;
}

.email-footer p {
  margin-bottom: 15px;
}

.footer-padded {
  padding: 0 45px;
}

/* Body Text ------------------------------ */

.email-text {
  font-size: 16px;
  line-height: 1.4;
  color: #51626f;
}

/* Button ------------------------------ */

.email-button {
  display: inline-block;
  width: 200px;
  background-color: #3869d4;
  border-radius: 3px;
  color: #ffffff;
  font-size: 16px;
  font-weight: bold;
  line-height: 50px;
  text-align: center;
  text-decoration: none;
  -webkit-text-size-adjust: none;
  mso-padding-alt: 10px 55px;
}

.email-button_inner {
  display: block;
  width: 200px;
  background-color: #3869d4;
  border-radius: 3px;
  font-size: 16px;
  font-weight: bold;
  line-height: 50px;
  text-align: center;
  text-decoration: none;
}

/* Attribute list ------------------------------ */

.email-attributes {
  margin: 0;
  padding: 0;
}

.email-attributes_content {
  display: table;
  width: 100%;
  padding: 16px;
  margin: 0;
  background-color: #f2f4f6;
  border-radius: 4px;
}

.email-attributes_item {
  padding: 0;
}

.email-attributes_content {
  display: table-cell;
  padding: 16px;
  margin: 0;
}

.email-attributes_item + .email-attributes_item {
  margin-left: 16px;
}

.email-attributes_heading {
  display: block;
  margin: 0 0 8px 0;
  font-size: 12px;
  font-weight: bold;
  line-height: 1;
  text-transform: uppercase;
  color: #2d3748;
}

.email-attributes_body {
  display: block;
  margin: 0;
  font-size: 15px;
  line-height: 1.4;
  color: #4a5568;
}

/* Related Items ------------------------------ */

.email-related {
  width: 100%;
  margin: 0;
  padding: 25px 0 0 0;
  -premailer-width: 100%;
  -premailer-cellpadding: 0;
  -premailer-cellspacing: 0;
}

.email-related_heading {
  margin: 0 0 16px 0;
  font-size: 16px;
  font-weight: bold;
  color: #2d3748;
}

.email-related_item {
  padding: 10px 0;
  color: #74788c;
  font-size: 15px;
  line-height: 1.5;
}

.email-related_item-title {
  display: block;
  margin: .5em 0 0;
}

.email-related_item-link {
  display: block;
  color: #3869d4;
  text-decoration: none;
}

/* Discount Code ------------------------------ */

.email-discount {
  width: 100%;
  margin: 0;
  padding: 24px;
  -premailer-width: 100%;
  -premailer-cellpadding: 0;
  -premailer-cellspacing: 0;
  background-color: #f2f4f6;
  border-radius: 4px;
}

.email-discount_heading {
  text-align: center;
}

.email-discount_body {
  display: table;
  width: 100%;
  margin: 16px 0 0 0;
  padding: 24px;
  -premailer-width: 100%;
  -premailer-cellpadding: 0;
  -premailer-cellspacing: 0;
  background-color: #ffffff;
  border-radius: 4px;
  text-align: center;
  font-weight: bold;
  font-size: 28px;
  color: #2d3748;
}

/* Social Icons ------------------------------ */

.email-social {
  width: 100%;
  margin: 0;
  padding: 0;
  -premailer-width: 100%;
  -premailer-cellpadding: 0;
  -premailer-cellspacing: 0;
}

.email-social_centered {
  text-align: center;
}

.email-social_centered .email-social_link {
  display: inline-block;
  margin: 0 8px;
}

.email-social_link {
  display: table-cell;
  padding: 0;
  width: auto;
  margin: 0;
  padding: 0;
  text-align: left;
  vertical-align: middle;
}

/* Data table ------------------------------ */

.email-data {
  width: 100%;
  margin: 27px 0 0 0;
  padding: 0;
  -premailer-cellpadding: 0;
  -premailer-cellspacing: 0;
  border-collapse: collapse;
  font-family: "Eigen", Menlo, Monaco, monospace;
  font-size: 14px;
}

.email-data_inner {
  width: 100%;
  max-width: 584px;
  -premailer-width: 584px;
  margin: 0 auto;
  padding: 0;
  -premailer-cellpadding: 0;
  -premailer-cellspacing: 0;
  background-color: #ffffff;
}

.email-data_row {
  padding: 10px;
  border-bottom: 1px solid #edeff2;
}

.email-data_columns {
  padding: 5px 0;
}

.email-data_column {
  word-break: break-word;
}

.email-data_column:not(:last-child) {
  padding-right: 16px;
}

.email-data_heading {
  padding: 0;
  margin: 0;
  padding-bottom: 8px;
  border-bottom: 2px solid #edeff2;
  font-weight: bold;
  color: #2d3748;
}

.email-data_cell {
  padding-bottom: 16px;
}

.email-data_cell_title {
  display: block;
  margin: .5em 0 0;
  font-weight: bold;
  line-height: 1.4;
  color: #2d3748;
}

.email-data_cell_list {
  margin: .5em 0 0;
  padding-left: 26px;
  list-style-position: left;
}

.email-data_cell_list li {
  list-style-type: disc;
  line-height: 1.4;
  color: #51626f;
}

.email-data_cell_list–value {
  font-weight: bold;
}

/* Trend ------------------------------ */

.email-trend {
  -moz-text-decoration-color: #c5cdd5;
  -webkit-text-decoration-color: #c5cdd5;
  text-decoration-color: #c5cdd5;
}

.email-trend.trending-up::after {
  content: "↗";
}

.email-trend.trending-down::after {
  content: "↘";
}

/* Media Queries ------------------------------ */

@media only screen and (max-width: 600px) {
  .email-body_inner,
  .email-footer {
    width: 100% !important;
    width: 100vw !important;
  }
}

@media only screen and (max-width: 500px) {
  .button {
    width: 100% !important;
  }

  .email-content {
    width: 100% !important;
  }

  .email-data_column:not(:last-child) {
    padding-right: 0;
  }

  .email-social_centered .email-social_link {
    display: block;
    margin: 8px 0;
  }
}

/* Preheader Text ------------------------------ */

@supports (mso-advanced-typography: enabled) {
  .preheader {
    display: none !important;
    font-size: 1px;
    max-height: 0;
    line-height: 1px;
    max-width: 0;
    opacity: 0;
    overflow: hidden;
    mso-hide: all;
    font-family: sans-serif;
  }
}

@media only screen and (max-width: 520px) {
  .email-masthead_name {
    font-size: 14px;
  }

  .email-button_inner {
    padding: 0 !important;
    display: block !important;
    width: auto !important;
    background-color: #3869d4;
    border-radius: 3px;
    font-size: 13px;
    font-weight: bold;
    line-height: 44px;
    text-align: center;
    text-decoration: none;
  }

  .email-button_inner {
    padding: 0 !important;
    display: block !important;
    width: auto !important;
    background-color: #3869d4;
    border-radius: 3px;
    font-size: 13px;
    font-weight: bold;
    line-height: 44px;
    text-align: center;
    text-decoration: none;
    border: 0;
  }

  .email-content {
    width: 100% !important;
  }

  .email-data_columns {
    padding: 0;
    column-count: 1 !important;
  }

  .email-left {
    padding-right: 0 !important;
  }

  .email-right {
    padding-left: 0 !important;
  }

  .email-masthead_name {
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
  }

  .header {
    padding-right: 0;
  }
}

body,
.email-body,
.email-body_inner,
.email-content,
.email-wrapper,
.email-masthead,
.email-masthead_name {
  --bg-color: #ffffff;
}

@media (prefers-color-scheme: dark) {
  body,
  .email-body,
  .email-body_inner,
  .email-wrapper,
  .email-masthead,
  .email-masthead_name {
    --bg-color: #1f2937 !important;
    background-color: var(--bg-color) !important;
  }

  p,
  ul,
  ol,
  blockquote,
  h1,
  h2,
  h3,
  span,
  .email-text {
    color: #f3f4f6 !important;
  }

  .email-masthead_name {
    text-shadow: 0px 1px 0px rgba(0, 0, 0, 0.4);
  }

  .email-footer {
    background-color: #111827 !important;
  }

  .email-masthead {
    border-bottom: 1px solid rgba(0, 0, 0, 0.4) !important;
  }

  .email-footer {
    border-top: 1px solid rgba(0, 0, 0, 0.4) !important;
  }

  .email-body {
    border-top: 1px solid rgba(0, 0, 0, 0.4) !important;
    border-bottom: 1px solid rgba(0, 0, 0, 0.4) !important;
  }

  .email-button_inner {
    border-color: #3869d4 !important;
    color: #f3f4f6 !important;
  }
}
</style>
</head>
<body style="width: 100%; margin: 0; padding: 0; -moz-osx-font-smoothing: grayscale; -webkit-font-smoothing: antialiased; background-color: #f2f4f6;">
  <span class="preheader">{{ $preheader ?? '' }}</span>
  <table class="email-wrapper" width="100%" cellpadding="0" cellspacing="0" role="presentation">
    <tr>
      <td align="center">
        <table class="email-content" width="100%" cellpadding="0" cellspacing="0" role="presentation">
          {{-- Header --}}
          <tr>
            <td class="email-masthead">
              <a href="{{ config('app.url') }}" class="email-masthead_name">
                {{ getAppTitleWithFallback() }}
              </a>
            </td>
          </tr>
          {{-- Body --}}
          <tr>
            <td class="email-body" width="100%" cellpadding="0" cellspacing="0">
              <table class="email-body_inner" align="center" width="100%" cellpadding="0" cellspacing="0" role="presentation">
                {{-- Body content --}}
                <tr>
                  <td class="email-body_inner" style="width: 100%; margin: 0; padding: 0;">
                    <table width="100%" cellpadding="0" cellspacing="0" role="presentation">
                      <tr>
                        <td class="footer-padded" style="width: 100%; margin: 0; padding: 0 45px;">
                          {{ Illuminate\Mail\Markdown::parse($slot) }}
                        </td>
                      </tr>
                    </table>
                  </td>
                </tr>
              </table>
            </td>
          </tr>
          {{-- Footer --}}
          <tr>
            <td>
              <table class="email-footer" width="100%" cellpadding="0" cellspacing="0" role="presentation">
                <tr>
                  <td class="footer-padded" align="center">
                    <p>© {{ date('Y') }} {{ getAppTitleWithFallback() }}. @lang('All rights reserved.')</p>
                  </td>
                </tr>
              </table>
            </td>
          </tr>
        </table>
      </td>
    </tr>
  </table>
</body>
</html>
