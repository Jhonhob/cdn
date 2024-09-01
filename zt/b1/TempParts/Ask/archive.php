<div id="ask-archive" class="ask-list" ref="asklist">
    <div class="gujia" ref="gujia">
        <?php
        for ($i = 0; $i < 12; $i++) {
            ?>
            <div class="ask-item b2flex">
                <div class="b2flex ask-meta-left">
                    <div class="ask-answer-count">
                    
                    </div>
                    <div class="ask-view-count">
                        
                    </div>
                </div>
                <div class="ask-item-info">
                    <div>
                        <h2 class="ask-title"></h2>
                        <div class="ask-item-footer b2flex">
                    <div class="ask-tags">
                        <span></span>
                        <span></span>
                    </div>
                    <div class="ask-metas">
                        <span></span>
                        <span></span>
                    </div>
                </div>
                    </div>
                </div>
                
            </div>
            <?php
        }
        ?>
    </div>
    <div class="ask-list-box" v-if="data != ''">
        <template v-if="empty">
            <div style="width:100%;height:100%">
                <?php echo B2_EMPTY; ?>
            </div>
        </template>
        <div class="ask-item b2flex" v-for="(item,i) in data.data" v-else>
            <div class="b2flex ask-meta-left">
                <div :class="['ask-answer-count',{'best':item.metas.best},{'has':item.metas.answer_count > 0}]">
                    <div><b v-text="item.metas.answer_count"></b></div>
                    <div>
                        <?php echo __('回答', 'b2'); ?>
                    </div>
                </div>
                <div class="ask-view-count">
                    <div><b v-text="item.metas.views"></b></div>
                    <div>
                        <?php echo __('浏览', 'b2'); ?>
                    </div>
                </div>
            </div>
            <div class="ask-item-info b2flex">
                <!-- <div class="ask-thumb" v-if="item.thumb">
                    <?php echo b2_get_img(
                        array(
                            'src_data' => ':src="item.thumb"',
                            'class' => array('ask-thumb-url'),
                            'source_data' => ':srcset="item.thumb_webp"'
                        )
                    ); ?>
                </div> -->
                <div class="ask-item-left">
                    <div class="ask-info-text">
                        <h2 class="ask-title">
                            <a :href="item.link" target="_blank">{{item.title}}</a>
                        </h2>
                        <a v-for="tag in item.tags" v-text="tag.name"
                            :style="'color:'+tag.color" class="ask-tag-item" :href="tag.link" target="_blank"></a>
                    </div>
                    <div class="ask-item-footer b2flex">
                        <div class="ask-pay b2flex">
                            <div class="ask-user b2flex">
                                <div class="ask-user-info">
                                    <a :href="item.author.link" target="_blank"><span class="ask-user-name"
                                            v-text="item.author.name"></span></a>
                                    <span class="ask-aks-date" v-text="dateToTime(item.metas._date)"></span>
                                </div>
                            </div>

                        </div>
                        <div class="ask-metas b2flex">
                            <div v-if="item.metas.reward" class="b2flex reward-meta">
                                <div class="ask-pay-number ask-widget-meta">
                                    <span v-if="item.metas.reward.rewardType == 'credit'" class="green">
                                        <?php echo b2_get_icon('b2-coin-line'); ?>
                                        <b v-text="item.metas.reward.money"></b>
                                    </span>
                                    <span v-else class="green">
                                        <?php echo B2_MONEY_SYMBOL; ?>
                                        <b v-text="item.metas.reward.money"></b>
                                    </span>
                                </div>
                                <!-- <div class="ask-pay-type">
                                    <span v-if="!item.metas.endtime" class="ask-passtime">
                                        <?php echo __('已过期', 'b2'); ?>
                                    </span>
                                    <span class="ask-passtime" v-else>
                                        <?php echo sprintf(__('%s后过期'), '<b v-text="item.metas.endtime"></b>'); ?>
                                    </span>
                                </div> -->
                            </div>
                            <span>
                                <?php echo sprintf(__('%s 收藏', 'b2'), '<b v-text="item.metas.favorites"></b>'); ?>
                            </span>
                            <span v-if="isAuthor && item.can_edit" v-cloak class="red" @click="deleteAsk(i,item.id)">
                                <?php echo __('删除', 'b2'); ?>
                            </span>
                            <span v-if="isAuthor && item.can_edit" v-cloak class="red"><a
                                    :href="'<?php echo b2_get_custom_page_url('po-ask'); ?>?id='+item.id"
                                    target="_blank"><?php echo __('编辑', 'b2'); ?></a></span>
                        </div>
                    </div>
                </div>
                <!-- <div class="ask-inv-box b2-radius" v-if="item.last_answer.name || item.metas.inv.length > 0">
                        <span v-if="item.metas.inv.length > 0">
                            <?php echo sprintf(__('%s邀请了%s回答此问题', 'b2'), '<b v-text="item.author.name"></b>', '<a :href="u.link" target="_blank" v-for="(u,ui) in item.metas.inv" :key="ui"><b v-text="u.name"></b></a>'); ?>
                        </span>
                        <span v-if="item.last_answer.name">
                            <?php echo sprintf(__(' 最后回答来自 %s'), '<a :href="item.last_answer.link" v-text="item.last_answer.name" target="_blank"></a>'); ?>
                        </span>
                    </div> -->
            </div>
        </div>
    </div>
</div>