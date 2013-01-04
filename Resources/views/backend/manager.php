<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Thinh Nguyen
 * Date: 12/20/12
 * Time: 10:05 AM
 * To change this template use File | Settings | File Templates.
 */
?>
<?php
$view['loader']->load(array('jquery.lib', 'bootstrap.lib', 'jquery.ui.lib', 'jquery.form.lib', 'riSeo:js/SeoScript.js', 'riSeo:css/main-style.css', 'riSeo:css/color.css'));
?>
<div class='title pull-left'><h3><?php $view['translator']->trans('Meta SEO Manager')?></h3></div>
<div class='clearfix'></div>
<div class="master-wrapper" id="seo-master">
    <div id="dialog-confirm" title="Delete meta?">
        <p><i class="icon-warning-sign"></i> Are you sure you want
            to delete this meta?</p>
    </div>
    <div class="content-presenter">
        <div class="ui-widget">
            <div id='selector'>
                <div id='default-page-selector'>
                    <label>Select a page to edit: </label>
                    <select id="combobox">
                        <option value="">Select one...</option>
                        <?php
                        foreach ($pages as $page) {
                            echo "<option value='" . $page . "'>" . $page . "</option>";
                        }

                        foreach ($ez_pages as $key => $value) {
                            echo "<option value=page-" . $key . ">" . $value . "</option>";
                        }
                        ?>
                    </select>
                </div>
            </div>

            <div id="meta-control">
                <div id="loading-holder">
                    <div id="loading">
                    </div>
                </div>
                <form id="meta-form" class="form-horizontal">
                    <ul>
                        <li><a href="#default-meta-input-group">Meta tag</a></li>
                        <li><a href="#additional-meta-input-group">Custom</a></li>
                    </ul>
                    <input type="hidden" name="seo-id" id="seo-id"/>
                    <input type="hidden" name="main-page" id="main-page"/>
                    <input type="hidden" name="page-id" id="page-id"/>

                    <div class="input-group" id="default-meta-input-group">
                        <div class="control-group">
                            <a class="control-label"
                               title="This will be the title of your page. If not set, the default title will get used."><?php $view['translator']->trans('Title') ?></a>

                            <div class="controls">
                                <textarea class="meta-input" name="metas[title]" id="meta-title"
                                          placeholder="Enter title..."></textarea>
                            </div>
                            <div class="helpers">
                                <input class="pull-left" type="text" value="0" maxlength="2" size="1"
                                       name="title-length"
                                       readonly=""/>
                                <label>characters. Most search engines use a maximum of 70 chars for the
                                    title.</label>
                            </div>
                        </div>

                        <div class="control-group">
                            <a class="control-label"
                               title="The META description for your page."><?php $view['translator']->trans('Description') ?></a>

                            <div class="controls">
                                <textarea class="meta-input" name="metas[description]" id="meta-description"
                                          placeholder="Enter meta description..."></textarea>
                            </div>
                            <div class="helpers">
                                <input class="pull-left" type="text" value="0" maxlength="2" size="1"
                                       name="description-length" readonly=""/>
                                <label>characters. Most search engines use a maximum of 160 chars for the
                                    description.</label>
                            </div>
                        </div>

                        <div class="control-group">
                            <a class="control-label"
                               title="A comma separated list of the most important keywords. Use optimal number of keywords."><?php $view['translator']->trans('Keywords') ?></a>

                            <div class="controls">
                                <textarea class="meta-input" name="metas[keywords]" id="meta-keywords"
                                          placeholder="Enter a comma separated list of keywords"></textarea>
                            </div>
                        </div>

                        <div class="control-group">
                            <a class="control-label"
                               title="Tell robots not to index the content of a page, and/or not scan it for links to follow."><?php $view['translator']->trans('Meta Index and Nofollow Tags:') ?></a>

                            <div class="controls" id="meta_robots">
                                <label class="radio" for="meta_robots_index_follow">
                                    <input type="radio" id="meta_robots_index_follow" type="radio" checked="1"
                                           value="index,follow" name="metas[robots]">
                                    index, follow
                                </label>
                                <label class="radio" for="meta_robots_index_nofollow">
                                    <input id="meta_robots_index_nofollow" type="radio" value="index,nofollow"
                                           name="metas[robots]">
                                    index, nofollow
                                </label>
                                <label class="radio" for="meta_robots_noindex_follow">
                                    <input id="meta_robots_noindex_follow" type="radio" value="noindex,follow"
                                           name="metas[robots]">
                                    noindex, follow
                                </label>
                                <label class="radio" for="meta_robots_noindex_nofollow">
                                    <input id="meta_robots_noindex_nofollow" type="radio" value="noindex,nofollow"
                                           name="metas[robots]">
                                    noindex, nofollow
                                </label>
                            </div>
                        </div>
                        <!--                        <div class="control-group">-->
                        <!--                            <a class="control-label"-->
                        <!--                               title=""">-->
                        <?php //$view['translator']->trans('Options') ?><!--</a>-->
                        <!--                            <div class="controls">-->
                        <!--                                <label class="checkbox">-->
                        <!--                                    <input type="checkbox" value="noarchive" name="metas[robots]">-->
                        <!--                                    NOARCHIVE-->
                        <!--                                </label>-->
                        <!--                                <label class="checkbox">-->
                        <!--                                    <input type="checkbox" value="nosnippet" name="metas[robots]">-->
                        <!--                                    NOSNIPPET-->
                        <!--                                </label>-->
                        <!--                            </div>-->
                        <!--                        </div>-->
                    </div>
                    <div class="input-group" id="additional-meta-input-group">
                        <div class="control-group" id="addtional-metas">
                            <a class="control-label"
                               title=""><?php $view['translator']->trans('Additional Meta Tag') ?></a>
                        </div>
                        <button title="Add new" type="button" class='btn btn-info' id='add-meta-button'
                                disabled="disabled"><i
                                class="icon-plus-sign icon-white"></i>
                        </button>
                    </div>

                    <div class="form-actions">
                        <button title="Save" type="submit" class='btn btn-primary' id='save-button' disabled="disabled">
                            <i class="icon-hdd icon-white"></i> Save
                        </button>
                        <!--                        <button title="Reset default" type="button" class='btn' id='default-button' disabled="disabled">-->
                        <!--                            Set default-->
                        <!--                        </button>-->
                    </div>
                    <div id="dialog" title="Notification" class="hide-if-no-js">
                        <p></p>
                    </div>
                </form>
            </div>
            <!--            <button title="Reset default" type="button" class='btn' id='toggle'>-->
            <!--                Toggle-->
            <!--            </button>-->
            <div class='clearBoth'></div>
        </div>
    </div>

</div>