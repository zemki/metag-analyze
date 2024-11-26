@extends('layouts.app')


@section('content')
<div class="container mx-auto">
    <p class="block text-gray-500 font-bold md:text-left mb-2 pr-4">Warning, translations are not visible until they
        are exported back to the app/lang file, using <code>php
            artisan translation:export</code> command or publish button.</p>
    <div class="bg-teal-100 border-t-4 border-teal-500 rounded-b text-teal-900 px-4 py-3 shadow-md"
         style="display:none;">
        <div class="flex">
            <div class="py-1">
                <svg class="fill-current mb-2 font-medium leading-tight text-base w-6 text-teal-500 mr-4" xmlns="http://www.w3.org/2000/svg"
                     viewBox="0 0 20 20">
                    <path
                        d="M2.93 17.07A10 10 0 1 1 17.07 2.93 10 10 0 0 1 2.93 17.07zm12.73-1.41A8 8 0 1 0 4.34 4.34a8 8 0 0 0 11.32 11.32zM9 11V9h2v6H9v-4zm0-6h2v2H9V5z"/>
                </svg>
            </div>
            <div>
                <p>Done importing, processed <strong class="counter">N</strong> items! Reload this page to refresh
                    the
                    groups!</p>
            </div>
        </div>
    </div>
    <div class="block text-teal-500 font-bold md:text-left mb-0 pr-4 mt-4"
         style="display:none;">
        <div class="flex">
            <div>
                <p>Done searching for translations, found <strong class="counter">N</strong> items!</p>
            </div>
        </div>
    </div>
    <div class="block text-teal-500 font-bold md:text-left mb-0 pr-4 mt-4"
         style="display:none;">
        <div class="flex">
            <div>
                <p>Done publishing the translations for group '<?php echo $group ?>'!</p>
            </div>
        </div>
    </div>
    <div class="block text-teal-500 font-bold md:text-left mb-0 pr-4 mt-4"
         style="display:none;">
        <div class="flex">
            <div>
                <p>Done publishing the translations for all group!</p>
            </div>
        </div>
    </div>
    <?php if(Session::has('successPublish')) : ?>
        <div class="block text-teal-500 font-bold md:text-left mb-0 pr-4 mt-4">
            <div class="flex">
                <div>
                    <?php echo Session::get('successPublish'); ?>
                </div>
            </div>
        </div>
    <?php endif; ?>
    <p>
        <?php if(!isset($group)) : ?>
    <form class="form-import" method="POST"
          action="<?php echo action('\Barryvdh\TranslationManager\Controller@postImport') ?>" data-remote="true"
          role="form">
        <input type="hidden" name="_token" value="<?php echo csrf_token(); ?>">
        <div class="mb-4">
            <div class="flex flex-wrap">
                <div class="sm:w-1/4 pr-4 pl-4">
                    <select name="replace"
                            class="block appearance-none w-1/3 bg-gray-200 border border-gray-200 text-gray-700 py-3 px-4 pr-8 rounded leading-tight focus:outline-none focus:bg-white focus:border-gray-500 my-2">
                        <option value="0">Append new translations</option>
                        <option value="1">Replace existing translations</option>
                    </select>
                </div>
                <div class="sm:w-1/5 pr-4 pl-4">
                    <button type="submit"
                            class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:ring my-2"
                            data-disable-with="Loading..">Import
                        groups
                    </button>
                </div>
            </div>
        </div>
    </form>
    <form class="form-find" method="POST"
          action="<?php echo action('\Barryvdh\TranslationManager\Controller@postFind') ?>" data-remote="true"
          role="form"
          data-confirm="Are you sure you want to scan you app folder? All found translation keys will be added to the database.">
        <div class="mb-4">
            <input type="hidden" name="_token" value="<?php echo csrf_token(); ?>">
            <button type="submit"
                    class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:ring my-2"
                    data-disable-with="Searching..">Find translations in files
            </button>
        </div>
    </form>
<?php endif; ?>
    <?php if(isset($group)) : ?>
        <form class="flex items-center form-publish" method="POST"
              action="<?php echo action('\Barryvdh\TranslationManager\Controller@postPublish', $group) ?>"
              data-remote="true" role="form"
              data-confirm="Are you sure you want to publish the translations group '<?php echo $group ?>? This will overwrite existing language files.">
            <input type="hidden" name="_token" value="<?php echo csrf_token(); ?>">
            <button type="submit"
                    class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:ring"
                    data-disable-with="Publishing..">Publish translations
            </button>
            <a href="<?= action('\Barryvdh\TranslationManager\Controller@getIndex') ?>"
               class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">Back</a>
        </form>
    <?php endif; ?>

    <form role="form" method="POST"
          action="<?php echo action('\Barryvdh\TranslationManager\Controller@postAddGroup') ?>">
        <input type="hidden" name="_token" value="<?php echo csrf_token(); ?>">
        <div class="mb-4">
            <p class="block text-gray-500 font-bold md:text-left mt-5 pr-4">Choose a group to display the group
                translations. If no groups are visisble, make sure you have run
                the migrations and imported the translations.</p>
            <select name="group" id="group"
                    class="group-select block appearance-none w-1/3 bg-gray-200 border border-gray-200 text-gray-700 py-3 px-4 pr-8 rounded leading-tight focus:outline-none focus:bg-white focus:border-gray-500 my-2">
                <?php foreach($groups as $key => $value): ?>
                    <option
                        value="<?php echo $key ?>"<?php echo $key == $group ? ' selected' : '' ?>><?php echo $value ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="mb-4">
            <label class="block text-gray-500 font-bold md:text-left mt-5 md:mb-0 pr-4">Enter a new group name and
                start edit translations in that group</label>
            <input type="text"
                   class="bg-gray-200 appearance-none border-2 border-gray-200 rounded w-1/3 py-2 px-4 text-gray-700 leading-tight focus:outline-none focus:bg-white focus:border-purple-500"
                   name="new-group"/>
        </div>
        <div class="mb-4">
            <input type="submit"
                   class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:ring mt-2"
                   name="add-group" value="Add and edit keys"/>
        </div>
    </form>
    <?php if($group): ?>
        <form action="<?php echo action('\Barryvdh\TranslationManager\Controller@postAdd', array($group)) ?>"
              method="POST" role="form" class="mt-2">
            <input type="hidden" name="_token" value="<?php echo csrf_token(); ?>">

            <label class="block text-gray-500 font-bold md:text-left mb-0 pr-4 mt-4">Add new keys to this
                group</label>
            <textarea
                class="mt-0 bg-gray-200 appearance-none border-2 border-gray-200 rounded w-1/3 py-2 px-4 text-gray-700 leading-tight focus:outline-none focus:bg-white focus:border-purple-500"
                rows="3" name="keys"
                placeholder="Add 1 key per line, without the group prefix"></textarea>

            <div class="mb-4">
                <input type="submit" value="Add keys"
                       class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:ring mt-2">
            </div>
        </form>
        <hr>
        <p class="mt-3">Total: <?= $numTranslations ?>, changed: <?= $numChanged ?></p>
        <table class="w-full max-w-full mb-4 bg-transparent">
            <thead>
            <tr>
                <th width="15%">Key</th>
                <?php foreach ($locales as $locale): ?>
                    <th><?= $locale ?></th>
                <?php endforeach; ?>
                <?php if ($deleteEnabled): ?>
                    <th>&nbsp;</th>
                <?php endif; ?>
            </tr>
            </thead>
            <tbody>

            <?php foreach ($translations as $key => $translation): ?>
                <tr class="border-2 border-gray-200 border-solid"
                    id="<?php echo htmlentities($key, ENT_QUOTES, 'UTF-8', false) ?>">
                    <td><?php echo htmlentities($key, ENT_QUOTES, 'UTF-8', false) ?></td>
                    <?php foreach ($locales as $locale): ?>
                        <?php $t = isset($translation[$locale]) ? $translation[$locale] : null ?>

                        <td>
                            <a href="#edit"
                               class="bg-gray-200 appearance-none border-2 border-gray-200 rounded py-2 px-4 text-gray-700 leading-tight focus:outline-none focus:bg-white focus:border-purple-500 editable status-<?php echo $t ? $t->status : 0 ?> locale-<?php echo $locale ?>"
                               data-locale="<?php echo $locale ?>"
                               data-name="<?php echo $locale . "|" . htmlentities($key, ENT_QUOTES, 'UTF-8', false) ?>"
                               data-type="textarea" data-pk="<?php echo $t ? $t->id : 0 ?>"
                               data-url="<?php echo $editUrl ?>"
                               data-title="Enter translation"><?php echo $t ? htmlentities($t->value, ENT_QUOTES, 'UTF-8', false) : '' ?></a>
                        </td>
                    <?php endforeach; ?>
                    <?php if ($deleteEnabled): ?>
                        <td>
                            <a href="<?php echo action('\Barryvdh\TranslationManager\Controller@postDelete', [$group, $key]) ?>"
                               class="delete-key"
                            >
                                <svg class="svg-icon fill-current w-5 mb-2 font-medium leading-tight text-lg mr-2" viewBox="0 0 20 20">
                                    <path
                                        d="M17.114,3.923h-4.589V2.427c0-0.252-0.207-0.459-0.46-0.459H7.935c-0.252,0-0.459,0.207-0.459,0.459v1.496h-4.59c-0.252,0-0.459,0.205-0.459,0.459c0,0.252,0.207,0.459,0.459,0.459h1.51v12.732c0,0.252,0.207,0.459,0.459,0.459h10.29c0.254,0,0.459-0.207,0.459-0.459V4.841h1.511c0.252,0,0.459-0.207,0.459-0.459C17.573,4.127,17.366,3.923,17.114,3.923M8.394,2.886h3.214v0.918H8.394V2.886z M14.686,17.114H5.314V4.841h9.372V17.114z M12.525,7.306v7.344c0,0.252-0.207,0.459-0.46,0.459s-0.458-0.207-0.458-0.459V7.306c0-0.254,0.205-0.459,0.458-0.459S12.525,7.051,12.525,7.306M8.394,7.306v7.344c0,0.252-0.207,0.459-0.459,0.459s-0.459-0.207-0.459-0.459V7.306c0-0.254,0.207-0.459,0.459-0.459S8.394,7.051,8.394,7.306"></path>
                                </svg>
                            </a>
                        </td>
                    <?php endif; ?>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <fieldset>
            <legend>Supported locales</legend>
            <p>
                Current supported locales:
            </p>
            <form class="form-remove-locale" method="POST" role="form"
                  action="<?php echo action('\Barryvdh\TranslationManager\Controller@postRemoveLocale') ?>"
                  data-confirm="Are you sure to remove this locale and all of data?">
                <input type="hidden" name="_token" value="<?php echo csrf_token(); ?>">
                <ul class="list-locales">
                    <?php foreach($locales as $locale): ?>
                        <li>
                            <div class="mb-4">
                                <button type="submit" name="remove-locale[<?php echo $locale ?>]"
                                        class="inline-block align-middle text-center select-none border font-normal whitespace-nowrap py-2 px-4 rounded text-base leading-normal no-underline text-red-lightest bg-red hover:bg-red-light btn-xs" data-disable-with="...">
                                    &times;
                                </button>
                                <?php echo $locale ?>

                            </div>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </form>
            <form class="form-add-locale" method="POST" role="form"
                  action="<?php echo action('\Barryvdh\TranslationManager\Controller@postAddLocale') ?>">
                <input type="hidden" name="_token" value="<?php echo csrf_token(); ?>">
                <div class="mb-4">
                    <p>
                        Enter new locale key:
                    </p>
                    <div class="flex flex-wrap">
                        <div class="sm:w-1/4 pr-4 pl-4">
                            <input type="text" name="new-locale"
                                   class="bg-gray-200 appearance-none border-2 border-gray-200 rounded w-1/3 py-2 px-4 text-gray-700 leading-tight focus:outline-none focus:bg-white focus:border-purple-500"/>
                        </div>
                        <div class="sm:w-1/5 pr-4 pl-4">
                            <button type="submit"
                                    class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:ring mt-2"
                                    data-disable-with="Adding..">Add new
                                locale
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </fieldset>
        <fieldset>
            <legend>Export all translations</legend>
            <form class="flex items-center form-publish-all" method="POST"
                  action="<?php echo action('\Barryvdh\TranslationManager\Controller@postPublish', '*') ?>"
                  data-remote="true" role="form"
                  data-confirm="Are you sure you want to publish all translations group? This will overwrite existing language files.">
                <input type="hidden" name="_token" value="<?php echo csrf_token(); ?>">
                <button type="submit"
                        class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:ring mt-2"
                        data-disable-with="Publishing..">Publish all
                </button>
            </form>
        </fieldset>

    <?php endif; ?>
</div>
@endsection

@section('pagespecificscripts')

<script src="//code.jquery.com/jquery-1.11.0.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/js/bootstrap.min.js"
        integrity="sha384-0mSbJDEHialfmuBBQP6A4Qrprq5OVfW37PRR3j5ELqxss1yVqOtnepnHVP9aJ7xS"
        crossorigin="anonymous"></script>
<!-- Latest compiled and minified CSS -->
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css"
      integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous">

<script
    src="//cdnjs.cloudflare.com/ajax/libs/x-editable/1.5.0/bootstrap3-editable/js/bootstrap-editable.min.js"></script>
<script>//https://github.com/rails/jquery-ujs/blob/master/src/rails.js
    (function (e, t) {
        if (e.rails !== t) {
            e.error("jquery-ujs has already been loaded!")
        }
        var n;
        var r = e(document);
        e.rails = n = {
            linkClickSelector: "a[data-confirm], a[data-method], a[data-remote], a[data-disable-with]",
            buttonClickSelector: "button[data-remote], button[data-confirm]",
            inputChangeSelector: "select[data-remote], input[data-remote], textarea[data-remote]",
            formSubmitSelector: "form",
            formInputClickSelector: "form input[type=submit], form input[type=image], form button[type=submit], form button:not([type])",
            disableSelector: "input[data-disable-with], button[data-disable-with], textarea[data-disable-with]",
            enableSelector: "input[data-disable-with]:disabled, button[data-disable-with]:disabled, textarea[data-disable-with]:disabled",
            requiredInputSelector: "input[name][required]:not([disabled]),textarea[name][required]:not([disabled])",
            fileInputSelector: "input[type=file]",
            linkDisableSelector: "a[data-disable-with]",
            buttonDisableSelector: "button[data-remote][data-disable-with]",
            CSRFProtection: function (t) {
                var n = e('meta[name="csrf-token"]').attr("content");
                if (n) t.setRequestHeader("X-CSRF-Token", n)
            },
            refreshCSRFTokens: function () {
                var t = e("meta[name=csrf-token]").attr("content");
                var n = e("meta[name=csrf-param]").attr("content");
                e('form input[name="' + n + '"]').val(t)
            },
            fire: function (t, n, r) {
                var i = e.Event(n);
                t.trigger(i, r);
                return i.result !== false
            },
            confirm: function (e) {
                return confirm(e)
            },
            ajax: function (t) {
                return e.ajax(t)
            },
            href: function (e) {
                return e.attr("href")
            },
            handleRemote: function (r) {
                var i, s, o, u, a, f, l, c;
                if (n.fire(r, "ajax:before")) {
                    u = r.data("cross-domain");
                    a = u === t ? null : u;
                    f = r.data("with-credentials") || null;
                    l = r.data("type") || e.ajaxSettings && e.ajaxSettings.dataType;
                    if (r.is("form")) {
                        i = r.attr("method");
                        s = r.attr("action");
                        o = r.serializeArray();
                        var h = r.data("ujs:submit-button");
                        if (h) {
                            o.push(h);
                            r.data("ujs:submit-button", null)
                        }
                    } else if (r.is(n.inputChangeSelector)) {
                        i = r.data("method");
                        s = r.data("url");
                        o = r.serialize();
                        if (r.data("params")) o = o + "&" + r.data("params")
                    } else if (r.is(n.buttonClickSelector)) {
                        i = r.data("method") || "get";
                        s = r.data("url");
                        o = r.serialize();
                        if (r.data("params")) o = o + "&" + r.data("params")
                    } else {
                        i = r.data("method");
                        s = n.href(r);
                        o = r.data("params") || null
                    }
                    c = {
                        type: i || "GET", data: o, dataType: l, beforeSend: function (e, i) {
                            if (i.dataType === t) {
                                e.setRequestHeader("accept", "*/*;q=0.5, " + i.accepts.script)
                            }
                            if (n.fire(r, "ajax:beforeSend", [e, i])) {
                                r.trigger("ajax:send", e)
                            } else {
                                return false
                            }
                        }, success: function (e, t, n) {
                            r.trigger("ajax:success", [e, t, n])
                        }, complete: function (e, t) {
                            r.trigger("ajax:complete", [e, t])
                        }, error: function (e, t, n) {
                            r.trigger("ajax:error", [e, t, n])
                        }, crossDomain: a
                    };
                    if (f) {
                        c.xhrFields = {withCredentials: f}
                    }
                    if (s) {
                        c.url = s
                    }
                    return n.ajax(c)
                } else {
                    return false
                }
            },
            handleMethod: function (r) {
                var i = n.href(r), s = r.data("method"), o = r.attr("target"),
                    u = e("meta[name=csrf-token]").attr("content"), a = e("meta[name=csrf-param]").attr("content"),
                    f = e('<form method="post" action="' + i + '"></form>'),
                    l = '<input name="_method" value="' + s + '" type="hidden" />';
                if (a !== t && u !== t) {
                    l += '<input name="' + a + '" value="' + u + '" type="hidden" />'
                }
                if (o) {
                    f.attr("target", o)
                }
                f.hide().append(l).appendTo("body");
                f.submit()
            },
            formElements: function (t, n) {
                return t.is("form") ? e(t[0].elements).filter(n) : t.find(n)
            },
            disableFormElements: function (t) {
                n.formElements(t, n.disableSelector).each(function () {
                    n.disableFormElement(e(this))
                })
            },
            disableFormElement: function (e) {
                var t = e.is("button") ? "html" : "val";
                e.data("ujs:enable-with", e[t]());
                e[t](e.data("disable-with"));
                e.prop("disabled", true)
            },
            enableFormElements: function (t) {
                n.formElements(t, n.enableSelector).each(function () {
                    n.enableFormElement(e(this))
                })
            },
            enableFormElement: function (e) {
                var t = e.is("button") ? "html" : "val";
                if (e.data("ujs:enable-with")) e[t](e.data("ujs:enable-with"));
                e.prop("disabled", false)
            },
            allowAction: function (e) {
                var t = e.data("confirm"), r = false, i;
                if (!t) {
                    return true
                }
                if (n.fire(e, "confirm")) {
                    r = n.confirm(t);
                    i = n.fire(e, "confirm:complete", [r])
                }
                return r && i
            },
            blankInputs: function (t, n, r) {
                var i = e(), s, o, u = n || "input,textarea", a = t.find(u);
                a.each(function () {
                    s = e(this);
                    o = s.is("input[type=checkbox],input[type=radio]") ? s.is(":checked") : s.val();
                    if (!o === !r) {
                        if (s.is("input[type=radio]") && a.filter('input[type=radio]:checked[name="' + s.attr("name") + '"]').length) {
                            return true
                        }
                        i = i.add(s)
                    }
                });
                return i.length ? i : false
            },
            nonBlankInputs: function (e, t) {
                return n.blankInputs(e, t, true)
            },
            stopEverything: function (t) {
                e(t.target).trigger("ujs:everythingStopped");
                t.stopImmediatePropagation();
                return false
            },
            disableElement: function (e) {
                e.data("ujs:enable-with", e.html());
                e.html(e.data("disable-with"));
                e.bind("click.railsDisable", function (e) {
                    return n.stopEverything(e)
                })
            },
            enableElement: function (e) {
                if (e.data("ujs:enable-with") !== t) {
                    e.html(e.data("ujs:enable-with"));
                    e.removeData("ujs:enable-with")
                }
                e.unbind("click.railsDisable")
            }
        };
        if (n.fire(r, "rails:attachBindings")) {
            e.ajaxPrefilter(function (e, t, r) {
                if (!e.crossDomain) {
                    n.CSRFProtection(r)
                }
            });
            r.delegate(n.linkDisableSelector, "ajax:complete", function () {
                n.enableElement(e(this))
            });
            r.delegate(n.buttonDisableSelector, "ajax:complete", function () {
                n.enableFormElement(e(this))
            });
            r.delegate(n.linkClickSelector, "click.rails", function (r) {
                var i = e(this), s = i.data("method"), o = i.data("params"), u = r.metaKey || r.ctrlKey;
                if (!n.allowAction(i)) return n.stopEverything(r);
                if (!u && i.is(n.linkDisableSelector)) n.disableElement(i);
                if (i.data("remote") !== t) {
                    if (u && (!s || s === "GET") && !o) {
                        return true
                    }
                    var a = n.handleRemote(i);
                    if (a === false) {
                        n.enableElement(i)
                    } else {
                        a.error(function () {
                            n.enableElement(i)
                        })
                    }
                    return false
                } else if (i.data("method")) {
                    n.handleMethod(i);
                    return false
                }
            });
            r.delegate(n.buttonClickSelector, "click.rails", function (t) {
                var r = e(this);
                if (!n.allowAction(r)) return n.stopEverything(t);
                if (r.is(n.buttonDisableSelector)) n.disableFormElement(r);
                var i = n.handleRemote(r);
                if (i === false) {
                    n.enableFormElement(r)
                } else {
                    i.error(function () {
                        n.enableFormElement(r)
                    })
                }
                return false
            });
            r.delegate(n.inputChangeSelector, "change.rails", function (t) {
                var r = e(this);
                if (!n.allowAction(r)) return n.stopEverything(t);
                n.handleRemote(r);
                return false
            });
            r.delegate(n.formSubmitSelector, "submit.rails", function (r) {
                var i = e(this), s = i.data("remote") !== t, o, u;
                if (!n.allowAction(i)) return n.stopEverything(r);
                if (i.attr("novalidate") == t) {
                    o = n.blankInputs(i, n.requiredInputSelector);
                    if (o && n.fire(i, "ajax:aborted:required", [o])) {
                        return n.stopEverything(r)
                    }
                }
                if (s) {
                    u = n.nonBlankInputs(i, n.fileInputSelector);
                    if (u) {
                        setTimeout(function () {
                            n.disableFormElements(i)
                        }, 13);
                        var a = n.fire(i, "ajax:aborted:file", [u]);
                        if (!a) {
                            setTimeout(function () {
                                n.enableFormElements(i)
                            }, 13)
                        }
                        return a
                    }
                    n.handleRemote(i);
                    return false
                } else {
                    setTimeout(function () {
                        n.disableFormElements(i)
                    }, 13)
                }
            });
            r.delegate(n.formInputClickSelector, "click.rails", function (t) {
                var r = e(this);
                if (!n.allowAction(r)) return n.stopEverything(t);
                var i = r.attr("name"), s = i ? {name: i, value: r.val()} : null;
                r.closest("form").data("ujs:submit-button", s)
            });
            r.delegate(n.formSubmitSelector, "ajax:send.rails", function (t) {
                if (this == t.target) n.disableFormElements(e(this))
            });
            r.delegate(n.formSubmitSelector, "ajax:complete.rails", function (t) {
                if (this == t.target) n.enableFormElements(e(this))
            });
            e(function () {
                n.refreshCSRFTokens()
            })
        }
    })(jQuery)
</script>

<script>
    jQuery(document).ready(function ($) {

        $.ajaxSetup({
            beforeSend: function (xhr, settings) {
                settings.data += "&_token=<?php echo csrf_token() ?>";
            }
        });

        $('.editable').editable().on('hidden', function (e, reason) {
            var locale = $(this).data('locale');
            if (reason === 'save') {
                $(this).removeClass('status-0').addClass('status-1');
            }
            if (reason === 'save' || reason === 'nochange') {
                var $next = $(this).closest('tr').next().find('.editable.locale-' + locale);
                setTimeout(function () {
                    $next.editable('show');
                }, 300);
            }
        });

        $('.group-select').on('change', function () {
            var group = $(this).val();
            if (group) {
                window.location.href = '<?php echo action('\Barryvdh\TranslationManager\Controller@getView') ?>/' + $(this).val();
            } else {
                window.location.href = '<?php echo action('\Barryvdh\TranslationManager\Controller@getIndex') ?>';
            }
        });

        $("a.delete-key").click(function (event) {
            event.preventDefault();
            if (confirm('Are you sure you want to delete the entry?')) {
                var row = $(this).closest('tr');
                var url = $(this).attr('href');
                var id = row.attr('id');
                $.post(url, {id: id}, function () {
                    row.remove();
                });
            }
        });

        $('.form-import').on('ajax:success', function (e, data) {
            $('div.success-import strong.counter').text(data.counter);
            $('div.success-import').slideDown();
            window.location.reload();
        });

        $('.form-find').on('ajax:success', function (e, data) {
            $('div.success-find strong.counter').text(data.counter);
            $('div.success-find').slideDown();
            window.location.reload();
        });

        $('.form-publish').on('ajax:success', function (e, data) {
            $('div.success-publish').slideDown();
        });

        $('.form-publish-all').on('ajax:success', function (e, data) {
            $('div.success-publish-all').slideDown();
        });

    })
</script>

@stop


