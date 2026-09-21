<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta content="IE=edge,chrome=1" http-equiv="X-UA-Compatible">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
    <title>Digimone API Documentation</title>

    <link href="https://fonts.googleapis.com/css?family=Open+Sans&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="{{ asset("/vendor/scribe/css/theme-default.style.css") }}" media="screen">
    <link rel="stylesheet" href="{{ asset("/vendor/scribe/css/theme-default.print.css") }}" media="print">

    <script src="https://cdn.jsdelivr.net/npm/lodash@4.17.10/lodash.min.js"></script>

    <link rel="stylesheet"
          href="https://unpkg.com/@highlightjs/cdn-assets@11.6.0/styles/obsidian.min.css">
    <script src="https://unpkg.com/@highlightjs/cdn-assets@11.6.0/highlight.min.js"></script>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/jets/0.14.1/jets.min.js"></script>

    <style id="language-style">
        /* starts out as display none and is replaced with js later  */
                    body .content .bash-example code { display: none; }
                    body .content .javascript-example code { display: none; }
            </style>

    <script>
        var tryItOutBaseUrl = "http://localhost:8000";
        var useCsrf = Boolean();
        var csrfUrl = "/sanctum/csrf-cookie";
    </script>
    <script src="{{ asset("/vendor/scribe/js/tryitout-5.11.0.js") }}"></script>

    <script src="{{ asset("/vendor/scribe/js/theme-default-5.11.0.js") }}"></script>

</head>

<body data-languages="[&quot;bash&quot;,&quot;javascript&quot;]">

<a href="#" id="nav-button">
    <span>
        MENU
        <img src="{{ asset("/vendor/scribe/images/navbar.png") }}" alt="navbar-image"/>
    </span>
</a>
<div class="tocify-wrapper">
    
            <div class="lang-selector">
                                            <button type="button" class="lang-button" data-language-name="bash">bash</button>
                                            <button type="button" class="lang-button" data-language-name="javascript">javascript</button>
                    </div>
    
    <div class="search">
        <input type="text" class="search" id="input-search" placeholder="Search">
    </div>

    <div id="toc">
                    <ul id="tocify-header-introduction" class="tocify-header">
                <li class="tocify-item level-1" data-unique="introduction">
                    <a href="#introduction">Introduction</a>
                </li>
                            </ul>
                    <ul id="tocify-header-authenticating-requests" class="tocify-header">
                <li class="tocify-item level-1" data-unique="authenticating-requests">
                    <a href="#authenticating-requests">Authenticating requests</a>
                </li>
                            </ul>
                    <ul id="tocify-header-manajemen-mutasi-bank" class="tocify-header">
                <li class="tocify-item level-1" data-unique="manajemen-mutasi-bank">
                    <a href="#manajemen-mutasi-bank">Manajemen Mutasi Bank</a>
                </li>
                                    <ul id="tocify-subheader-manajemen-mutasi-bank" class="tocify-subheader">
                                                    <li class="tocify-item level-2" data-unique="manajemen-mutasi-bank-GETapi-bank-mutations">
                                <a href="#manajemen-mutasi-bank-GETapi-bank-mutations">Ambil Daftar Mutasi Bank</a>
                            </li>
                                                                                <li class="tocify-item level-2" data-unique="manajemen-mutasi-bank-GETapi-bank-mutations-unmatched-count">
                                <a href="#manajemen-mutasi-bank-GETapi-bank-mutations-unmatched-count">Hitung Mutasi Belum Dicocokkan</a>
                            </li>
                                                                                <li class="tocify-item level-2" data-unique="manajemen-mutasi-bank-GETapi-bank-mutations--id-">
                                <a href="#manajemen-mutasi-bank-GETapi-bank-mutations--id-">Ambil Detail Mutasi Bank</a>
                            </li>
                                                                        </ul>
                            </ul>
            </div>

    <ul class="toc-footer" id="toc-footer">
                    <li style="padding-bottom: 5px;"><a href="{{ route("scribe.postman") }}">View Postman collection</a></li>
                            <li style="padding-bottom: 5px;"><a href="{{ route("scribe.openapi") }}">View OpenAPI spec</a></li>
                <li><a href="http://github.com/knuckleswtf/scribe">Documentation powered by Scribe ✍</a></li>
    </ul>

    <ul class="toc-footer" id="last-updated">
        <li>Last updated: September 9, 2026</li>
    </ul>
</div>

<div class="page-wrapper">
    <div class="dark-box"></div>
    <div class="content">
        <h1 id="introduction">Introduction</h1>
<p>Ini adalah dokumentasi API untuk aplikasi Digimone.</p>
<aside>
    <strong>Base URL</strong>: <code>http://localhost:8000</code>
</aside>
<pre><code>Dokumentasi ini dibuat menggunakan [Scribe](https://scribe.knuckles.wtf/laravel) untuk memudahkan pengembang dalam memahami dan mengintegrasikan API yang tersedia.

Untuk mengakses endpoint API ini, Anda memerlukan kunci API. Silakan hubungi tim pengembang untuk mendapatkan kunci akses tersebut, lalu sertakan pada setiap permintaan ke endpoint yang memerlukan autentikasi.

Jika Anda memiliki pertanyaan atau memerlukan bantuan lebih lanjut, silakan hubungi tim pengembang kami melalui saluran dukungan resmi. Terima kasih telah menggunakan API kami!</code></pre>

        <h1 id="authenticating-requests">Authenticating requests</h1>
<p>To authenticate requests, include a <strong><code>X-API-KEY</code></strong> header with the value <strong><code>"cth: secret_key_123"</code></strong>.</p>
<p>All authenticated endpoints are marked with a <code>requires authentication</code> badge in the documentation below.</p>
<p>Semua endpoint dalam API ini dilindungi. Anda harus menyertakan API Key pada setiap request.</p>

        <h1 id="manajemen-mutasi-bank">Manajemen Mutasi Bank</h1>

    <p>Endpoint untuk mengelola dan melihat data mutasi bank serta proses rekonsiliasi.</p>

                                <h2 id="manajemen-mutasi-bank-GETapi-bank-mutations">Ambil Daftar Mutasi Bank</h2>

<p>
<small class="badge badge-darkred">requires authentication</small>
</p>

<p>Menampilkan data mutasi bank yang belum dicocokkan (is_matched = false)
dengan opsi filter berdasarkan kode bank dan rentang tanggal transaksi.</p>

<span id="example-requests-GETapi-bank-mutations">
<blockquote>Example request:</blockquote>


<div class="bash-example">
    <pre><code class="language-bash">curl --request GET \
    --get "http://localhost:8000/api/bank-mutations?is_matched=false&amp;bank_code=0002&amp;start_date=2026-01-01&amp;end_date=2026-01-02" \
    --header "X-API-KEY: cth: secret_key_123" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json"</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "http://localhost:8000/api/bank-mutations"
);

const params = {
    "is_matched": "false",
    "bank_code": "0002",
    "start_date": "2026-01-01",
    "end_date": "2026-01-02",
};
Object.keys(params)
    .forEach(key =&gt; url.searchParams.append(key, params[key]));

const headers = {
    "X-API-KEY": "cth: secret_key_123",
    "Content-Type": "application/json",
    "Accept": "application/json",
};


fetch(url, {
    method: "GET",
    headers,
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-GETapi-bank-mutations">
            <blockquote>
            <p>Example response (200):</p>
        </blockquote>
                <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;status&quot;: true,
    &quot;message&quot;: &quot;Data mutasi bank berhasil diambil.&quot;,
    &quot;filters&quot;: {
        &quot;is_matched&quot;: false,
        &quot;bank_code&quot;: null,
        &quot;start_date&quot;: &quot;2026-08-01&quot;,
        &quot;end_date&quot;: &quot;2026-08-02&quot;
    },
    &quot;total_records&quot;: 2,
    &quot;data&quot;: [
        {
            &quot;id&quot;: 1,
            &quot;journal_id&quot;: null,
            &quot;bank_account_id&quot;: null,
            &quot;source_id&quot;: null,
            &quot;bank_code&quot;: &quot;1&quot;,
            &quot;bank_name&quot;: &quot;a&quot;,
            &quot;debit&quot;: &quot;0.00&quot;,
            &quot;credit&quot;: &quot;100.00&quot;,
            &quot;transaction_date&quot;: &quot;2026-08-01T00:00:00.000000Z&quot;,
            &quot;transaction_time&quot;: null,
            &quot;description&quot;: &quot;test&quot;,
            &quot;reference&quot;: &quot;trx-12345&quot;,
            &quot;is_matched&quot;: false,
            &quot;created_at&quot;: &quot;2026-08-31T07:41:33.000000Z&quot;,
            &quot;updated_at&quot;: &quot;2026-08-31T07:41:33.000000Z&quot;,
            &quot;deleted_at&quot;: null
        },
        {
            &quot;id&quot;: 2,
            &quot;journal_id&quot;: null,
            &quot;bank_account_id&quot;: null,
            &quot;source_id&quot;: null,
            &quot;bank_code&quot;: &quot;2&quot;,
            &quot;bank_name&quot;: &quot;b&quot;,
            &quot;debit&quot;: &quot;100.00&quot;,
            &quot;credit&quot;: &quot;0.00&quot;,
            &quot;transaction_date&quot;: &quot;2026-08-02T00:00:00.000000Z&quot;,
            &quot;transaction_time&quot;: null,
            &quot;description&quot;: &quot;test1&quot;,
            &quot;reference&quot;: &quot;trx-12346&quot;,
            &quot;is_matched&quot;: false,
            &quot;created_at&quot;: &quot;2026-08-31T07:41:33.000000Z&quot;,
            &quot;updated_at&quot;: &quot;2026-08-31T07:41:33.000000Z&quot;,
            &quot;deleted_at&quot;: null
        }
    ]
}</code>
 </pre>
    </span>
<span id="execution-results-GETapi-bank-mutations" hidden>
    <blockquote>Received response<span
                id="execution-response-status-GETapi-bank-mutations"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-GETapi-bank-mutations"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-GETapi-bank-mutations" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-GETapi-bank-mutations">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-GETapi-bank-mutations" data-method="GET"
      data-path="api/bank-mutations"
      data-authed="1"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('GETapi-bank-mutations', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-GETapi-bank-mutations"
                    onclick="tryItOut('GETapi-bank-mutations');">Try it out ⚡
            </button>
            <button type="button"
                    style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-GETapi-bank-mutations"
                    onclick="cancelTryOut('GETapi-bank-mutations');" hidden>Cancel 🛑
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-GETapi-bank-mutations"
                    data-initial-text="Send Request 💥"
                    data-loading-text="⏱ Sending..."
                    hidden>Send Request 💥
            </button>
            </h3>
            <p>
            <small class="badge badge-green">GET</small>
            <b><code>api/bank-mutations</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>X-API-KEY</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="X-API-KEY" class="auth-value"               data-endpoint="GETapi-bank-mutations"
               value="cth: secret_key_123"
               data-component="header">
    <br>
<p>Example: <code>cth: secret_key_123</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="GETapi-bank-mutations"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="GETapi-bank-mutations"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                            <h4 class="fancy-heading-panel"><b>Query Parameters</b></h4>
                                    <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>is_matched</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="is_matched"                data-endpoint="GETapi-bank-mutations"
               value="false"
               data-component="query">
    <br>
<p>Status penyesuaian (true atau false). Example: <code>false</code></p>
            </div>
                                    <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>bank_code</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="bank_code"                data-endpoint="GETapi-bank-mutations"
               value="0002"
               data-component="query">
    <br>
<p>Kode bank (contoh: 0001, 0002). Example: <code>0002</code></p>
            </div>
                                    <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>start_date</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="start_date"                data-endpoint="GETapi-bank-mutations"
               value="2026-01-01"
               data-component="query">
    <br>
<p>Tanggal awal transaksi dengan format YYYY-MM-DD. Example: <code>2026-01-01</code></p>
            </div>
                                    <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>end_date</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="end_date"                data-endpoint="GETapi-bank-mutations"
               value="2026-01-02"
               data-component="query">
    <br>
<p>Tanggal akhir transaksi dengan format YYYY-MM-DD. Example: <code>2026-01-02</code></p>
            </div>
                </form>

                    <h2 id="manajemen-mutasi-bank-GETapi-bank-mutations-unmatched-count">Hitung Mutasi Belum Dicocokkan</h2>

<p>
<small class="badge badge-darkred">requires authentication</small>
</p>

<p>Mengembalikan jumlah total data mutasi bank yang belum dicocokkan (is_matched = false).</p>

<span id="example-requests-GETapi-bank-mutations-unmatched-count">
<blockquote>Example request:</blockquote>


<div class="bash-example">
    <pre><code class="language-bash">curl --request GET \
    --get "http://localhost:8000/api/bank-mutations/unmatched-count" \
    --header "X-API-KEY: cth: secret_key_123" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json"</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "http://localhost:8000/api/bank-mutations/unmatched-count"
);

const headers = {
    "X-API-KEY": "cth: secret_key_123",
    "Content-Type": "application/json",
    "Accept": "application/json",
};


fetch(url, {
    method: "GET",
    headers,
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-GETapi-bank-mutations-unmatched-count">
            <blockquote>
            <p>Example response (200):</p>
        </blockquote>
                <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;success&quot;: true,
    &quot;message&quot;: &quot;Jumlah data mutasi bank yang belum dicocokkan berhasil diambil.&quot;,
    &quot;data&quot;: {
        &quot;total_unmatched&quot;: 3
    }
}</code>
 </pre>
    </span>
<span id="execution-results-GETapi-bank-mutations-unmatched-count" hidden>
    <blockquote>Received response<span
                id="execution-response-status-GETapi-bank-mutations-unmatched-count"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-GETapi-bank-mutations-unmatched-count"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-GETapi-bank-mutations-unmatched-count" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-GETapi-bank-mutations-unmatched-count">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-GETapi-bank-mutations-unmatched-count" data-method="GET"
      data-path="api/bank-mutations/unmatched-count"
      data-authed="1"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('GETapi-bank-mutations-unmatched-count', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-GETapi-bank-mutations-unmatched-count"
                    onclick="tryItOut('GETapi-bank-mutations-unmatched-count');">Try it out ⚡
            </button>
            <button type="button"
                    style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-GETapi-bank-mutations-unmatched-count"
                    onclick="cancelTryOut('GETapi-bank-mutations-unmatched-count');" hidden>Cancel 🛑
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-GETapi-bank-mutations-unmatched-count"
                    data-initial-text="Send Request 💥"
                    data-loading-text="⏱ Sending..."
                    hidden>Send Request 💥
            </button>
            </h3>
            <p>
            <small class="badge badge-green">GET</small>
            <b><code>api/bank-mutations/unmatched-count</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>X-API-KEY</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="X-API-KEY" class="auth-value"               data-endpoint="GETapi-bank-mutations-unmatched-count"
               value="cth: secret_key_123"
               data-component="header">
    <br>
<p>Example: <code>cth: secret_key_123</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="GETapi-bank-mutations-unmatched-count"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="GETapi-bank-mutations-unmatched-count"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                        </form>

                    <h2 id="manajemen-mutasi-bank-GETapi-bank-mutations--id-">Ambil Detail Mutasi Bank</h2>

<p>
<small class="badge badge-darkred">requires authentication</small>
</p>

<p>Mengambil detail data mutasi bank berdasarkan ID.</p>

<span id="example-requests-GETapi-bank-mutations--id-">
<blockquote>Example request:</blockquote>


<div class="bash-example">
    <pre><code class="language-bash">curl --request GET \
    --get "http://localhost:8000/api/bank-mutations/1" \
    --header "X-API-KEY: cth: secret_key_123" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json"</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "http://localhost:8000/api/bank-mutations/1"
);

const headers = {
    "X-API-KEY": "cth: secret_key_123",
    "Content-Type": "application/json",
    "Accept": "application/json",
};


fetch(url, {
    method: "GET",
    headers,
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-GETapi-bank-mutations--id-">
            <blockquote>
            <p>Example response (200):</p>
        </blockquote>
                <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;success&quot;: true,
    &quot;message&quot;: &quot;Data mutasi bank berhasil diambil.&quot;,
    &quot;data&quot;: {
        &quot;id&quot;: 1,
        &quot;journal_id&quot;: null,
        &quot;bank_account_id&quot;: null,
        &quot;source_id&quot;: null,
        &quot;bank_code&quot;: &quot;1&quot;,
        &quot;bank_name&quot;: &quot;a&quot;,
        &quot;debit&quot;: &quot;0.00&quot;,
        &quot;credit&quot;: &quot;100.00&quot;,
        &quot;transaction_date&quot;: &quot;2026-08-01T00:00:00.000000Z&quot;,
        &quot;transaction_time&quot;: null,
        &quot;description&quot;: &quot;test&quot;,
        &quot;reference&quot;: &quot;trx-12345&quot;,
        &quot;is_matched&quot;: false,
        &quot;created_at&quot;: &quot;2026-08-31T07:41:33.000000Z&quot;,
        &quot;updated_at&quot;: &quot;2026-08-31T07:41:33.000000Z&quot;,
        &quot;deleted_at&quot;: null
    }
}</code>
 </pre>
    </span>
<span id="execution-results-GETapi-bank-mutations--id-" hidden>
    <blockquote>Received response<span
                id="execution-response-status-GETapi-bank-mutations--id-"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-GETapi-bank-mutations--id-"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-GETapi-bank-mutations--id-" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-GETapi-bank-mutations--id-">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-GETapi-bank-mutations--id-" data-method="GET"
      data-path="api/bank-mutations/{id}"
      data-authed="1"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('GETapi-bank-mutations--id-', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-GETapi-bank-mutations--id-"
                    onclick="tryItOut('GETapi-bank-mutations--id-');">Try it out ⚡
            </button>
            <button type="button"
                    style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-GETapi-bank-mutations--id-"
                    onclick="cancelTryOut('GETapi-bank-mutations--id-');" hidden>Cancel 🛑
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-GETapi-bank-mutations--id-"
                    data-initial-text="Send Request 💥"
                    data-loading-text="⏱ Sending..."
                    hidden>Send Request 💥
            </button>
            </h3>
            <p>
            <small class="badge badge-green">GET</small>
            <b><code>api/bank-mutations/{id}</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>X-API-KEY</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="X-API-KEY" class="auth-value"               data-endpoint="GETapi-bank-mutations--id-"
               value="cth: secret_key_123"
               data-component="header">
    <br>
<p>Example: <code>cth: secret_key_123</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="GETapi-bank-mutations--id-"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="GETapi-bank-mutations--id-"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                        <h4 class="fancy-heading-panel"><b>URL Parameters</b></h4>
                    <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>id</code></b>&nbsp;&nbsp;
<small>integer</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="number" style="display: none"
               step="any"               name="id"                data-endpoint="GETapi-bank-mutations--id-"
               value="1"
               data-component="url">
    <br>
<p>ID dari mutasi bank. Example: <code>1</code></p>
            </div>
                    </form>

            

        
    </div>
    <div class="dark-box">
                    <div class="lang-selector">
                                                        <button type="button" class="lang-button" data-language-name="bash">bash</button>
                                                        <button type="button" class="lang-button" data-language-name="javascript">javascript</button>
                            </div>
            </div>
</div>
</body>
</html>
