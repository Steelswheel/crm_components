<template>
    <div class="comments" id="comments">
        <el-form
            class="comments-form"
            @submit.native.prevent
            :rules="rules"
            :model="form"
            label-position="top"
            ref="comments-form"
            size="mini"
        >
            <el-form-item
                prop="text"
            >
                <el-input
                    @focus="showFormElems"
                    autosize
                    placeholder="Добавить комментарий"
                    size="small"
                    type="textarea"
                    v-model="form.text"
                />
            </el-form-item>
            <el-form-item
                class="d-flex align-items-center mt-4"
                v-if="showForm"
            >
                <el-button
                    native-type="submit"
                    type="info"
                    @click="addComment"
                    :loading="loading"
                >
                    Отправить
                </el-button>

                <el-switch
                    class="ml-4"
                    active-text="Виден партнеру"
                    v-model="form.show_to_partner"
                />
            </el-form-item>
        </el-form>
        
        <template v-if="comments.length > 0">
            <div
                :class="comment.FROM_PARTNER || comment.SHOW_TO_PARTNER ? 'comments-item partner mt-2 mb-2' : 'comments-item mt-2 mb-2'"
                v-for="(comment, index) in comments"
                v-show="index < commentsCount"
                :key="comment.ID"
            >
                <div
                    v-if="comment.FROM_PARTNER"
                    class="comments-item-partner-head"
                >
                    Комментарий от партнера
                </div>
                <div
                    v-if="!comment.FROM_PARTNER && comment.SHOW_TO_PARTNER"
                    class="comments-item-partner-head"
                >
                    Комментарий для партнера
                </div>
                <div class="comments-item-header d-flex align-items-center justify-content-between">
                    <div class="d-flex align-items-center">
                        <div
                            v-if="comment.AUTHOR_PHOTO"
                            :src="comment.AUTHOR_PHOTO"
                            :style="`background-image: url(${comment.AUTHOR_PHOTO})`"
                            class="comments-item-header-photo mr-2"
                        ></div>
                        <a
                            class="comments-item-header-name"
                            :href="`/company/personal/user/${comment.AUTHOR_ID}/`"
                            target="_blank"
                            :title="comment.AUTHOR_NAME"
                        >
                            {{ comment.AUTHOR_NAME }}
                        </a>
                    </div>
                    <div class="comments-item-header-date">
                        {{ comment.DATE }}
                    </div>
                </div>
                <div>
                    <div
                        class="comments-item-content hidden-text mt-2"
                        v-html="comment.TEXT"
                        :ref="`comment-content-${comment.ID}`"
                    >
                    </div>
                    <span
                        class="comments-item-content-show"
                        v-show="comment.TEXT.length > 431"
                        @click="switchVisibility($event, `comment-content-${comment.ID}`)"
                    >
                        Показать полностью
                    </span>
                </div>
                <div
                    class="comments-item-files mt-2"
                    v-if="comment.FILES && comment.FILES.length > 0"
                >
                    <b>Файлы: </b>
                    <a
                        class="ml-1 mr-1"
                        target="_blank"
                        :href="`/bitrix/tools/disk/uf.php?attachedId=${file.ATTACHED_ID}&action=download&ncc=1`"
                        v-for="file in comment.FILES"
                        :key="file.ATTACHED_ID"
                    >
                        {{ file.NAME }}
                    </a>
                </div>
                <div
                    class="comments-item-delete"
                    @click="deleteComment(index, comment.ID)"
                    v-if="isLessHour(comment.DATE) && isAdmin && !comment.OLD_COMMENT && !comment.FROM_PARTNER"
                >
                    <i class="el-icon-delete"></i>
                </div>
            </div>
            <el-button
                v-if="comments && comments.length > 50"
                @click="showComments"
            >
                {{ this.commentsHidden ? `Показать следующие ${comments.length - commentsCount} ${declOfNum(comments.length - commentsCount)}` : 'Скрыть комментарии' }}
            </el-button>
        </template>
        <el-empty
            v-else
            description="Нет комментариев"
            image="/local/components/vaganov/comments/img/message.png"
            :image-size="70"
        >
        </el-empty>
    </div>
</template>

<script>
/*global BX*/
import {
    Form,
    FormItem,
    Input,
    Button,
    Empty,
    Switch,
    Loading
} from 'element-ui';

import { BX_POST } from '@app/API';
import { cloneDeep } from 'lodash';
import moment from 'moment';

export default {
    name: 'comments',
    props: {
        isAdmin: Boolean,
        dealId: String || Number,
        userId: String || Number,
        oldComments: {}
    },
    components: {
        'el-form': Form,
        'el-empty': Empty,
        'el-input': Input,
        'el-button': Button,
        'el-switch': Switch,
        'el-form-item': FormItem
    },
    data() {
        return {
            commentsHidden: true,
            commentsCount: 49,
            loading: false,
            showForm: false,
            comments: [],
            form: {
                from_partner: false,
                show_to_partner: false,
                text: '',
                files: [],
                formData: new BX.ajax.FormData()
            },
            rules: {
                text: [{ required: true, message: 'Введите текст комментария', trigger: 'change' }]
            },
            load: false
        }
    },
    methods: {
        isLessHour(date) {
            if (date) {
                const currentDate = new Date();
                const commentDate = moment(date, 'DD.MM.YYYY').toDate();

                let isHour = currentDate.getHours() - commentDate.getHours() < 1;

                if (currentDate.getDate() === commentDate.getDate() && currentDate.getMonth() === commentDate.getMonth() && currentDate.getFullYear() === commentDate.getFullYear() && isHour) {
                    return true;
                }
            }

            return false;
        },
        deleteComment(index, id) {
            this.comments.splice(index, 1);

            BX_POST('vaganov:comments', 'deleteComment', {id});
        },
        declOfNum(n) {
            const text_forms = ['комментарий', 'комментария', 'комментариев'];

            n = Math.abs(n) % 100;

            let n1 = n % 10;

            if (n > 10 && n < 20) {
                return text_forms[2];
            }

            if (n1 > 1 && n1 < 5) {
                return text_forms[1];
            }

            if (n1 === 1) {
                return text_forms[0];
            }

            return text_forms[2];
        },
        showFormElems() {
            const onClickOutside = event => this.showForm = this.$el.contains(event.target) && event.target.closest('.comments-form');
            document.addEventListener('click', onClickOutside);
            this.$on('hook:beforeDestroy', () => document.removeEventListener('click', onClickOutside));
        },
        showComments() {
            this.commentsHidden = !this.commentsHidden;
            
            if (this.commentsHidden) {
                this.commentsCount = 49;

                window.scrollTo({
                    top: 0,
                    behavior: 'smooth'
                });
            } else {
                this.commentsCount = this.comments.length;
            }
        },
        switchVisibility(event, link) {
            const element = this.$refs[link][0];

            if (element.classList.contains('hidden-text')) {
                element.classList.remove('hidden-text');
                event.target.innerHTML = 'Свернуть';
            } else {
                element.classList.add('hidden-text');
                event.target.innerHTML = 'Показать полностью';
            }
        },
        getComments() {
            BX_POST('vaganov:comments', 'getComments', {
                dealId: this.dealId
            }).then(r => {
                this.comments = r;
                this.oldCommentsParser();
                this.comments.sort((prev, next) => {
                  if (prev.DATE && next.DATE) {
                    let prevDate = moment(prev.DATE, 'DD.MM.YYYY').toDate();
                    let nextDate = moment(next.DATE, 'DD.MM.YYYY').toDate();

                    return prevDate > nextDate ? -1 : 1;
                  }
                });
            }).catch(e => {
                console.log(e);
            });
        },
        addComment() {
            if (this.form.text.length > 0) {
                let data = cloneDeep(this.form);
                data.text = data.text.replace(/\n/g, "<br>");

                this.load = Loading.service({
                    target: '#comments',
                    fullscreen: true,
                    background: '000'
                });

                BX_POST('vaganov:comments', 'addComment', {
                    data: JSON.stringify(data),
                    dealId: this.dealId
                }).then(r => {
                    if (r) {
                        this.$refs['comments-form'].resetFields();
                        this.showForm = false;
                    }
                }).catch(e => {
                    console.log(e);
                }).finally(() => {
                    this.loading = false;
                });
            }
        },
        oldCommentsParser() {
            let old = [];

            if (this.oldComments && this.oldComments.COMMENTS) {
                let str = this.oldComments.COMMENTS.replace(/\n/gi, "<br>");
                let arr = str.split("<br>");

                arr.forEach(item => {
                    if (item) {
                        old.push(item.replace(/\r/gi, '').trim());
                    }
                });

                old.forEach(item => {
                    let regex = /^(\d{2}.\d{2}.\d{4}) - (.*) \(([а-яА-ЯёЁ]+)\)/gm;
                    let m = regex.exec(item);

                    if (m) {
                        this.comments.push({
                            DATE: m[1],
                            TEXT: m[2],
                            AUTHOR_NAME: m[3],
                            OLD_COMMENT: true
                        });
                    } else {
                        this.comments.push({
                            TEXT: item,
                            OLD_COMMENT: true
                        });
                    }
                });
            }
        }
    },
    mounted() {
        const self = this;

        self.getComments();

        BX.addCustomEvent('onPullEvent-pull_comments', BX.delegate((command) => {
            if (command === 'add_comment') {
                let promise = new Promise(function(resolve) {
                    resolve(self.getComments());
                });

                promise.then(() => {
                    if (self.load) {
                        self.load.close();
                    }
                });
            }
        }, self));
    }
}
</script>

<style lang="scss">
    .comments {
        max-width: 384px;

        .el-textarea__inner {
            border: none;
            padding: 9px 10px;
            min-height: 40px;

            &:focus {
                border: 1px solid #DCDFE6;
            }
        }

        //.hidden-text {
        //    display: -webkit-box;
        //    -webkit-line-clamp: 15;
        //    -webkit-box-orient: vertical;
        //    overflow: hidden;
        //    text-overflow: ellipsis;
        //}

        &-form {
            background-color: #fff;
            padding: 5px 12px 20px 12px;

            .el-form-item:last-child {
                margin: 0;
            }
        }

        &-item {
            position: relative;
            background-color: #FFF;
            padding: 3px 8px 8px 5px;
            box-shadow: 0 2px 5px 0 rgba(0, 0 , 0, .15);

            &-files {
                font-size: 12px;
            }

            &-delete {
                position: absolute;
                bottom: 5px;
                right: 8px;
                transition: color .15s linear;
                cursor: pointer;

                &:hover {
                    color: #5cb6ff;
                }
            }

            &-partner-head {
                color: #000;
                font-size: 14px;
                line-height: 120%;
                margin-bottom: 2px;
            }

            &.partner {
                background-color: #FFE4E4;
            }

            &-header {
                &-photo {
                    width: 25px;
                    height: 25px;
                    border-radius: 50%;
                    margin-right: 2px;
                    background-size: cover;
                    background-repeat: no-repeat;
                    background-position: center center;
                }

                &-name {
                    font-size: 14px;
                    line-height: 120%;
                    color: #333333;
                }

                &-date {
                    color: #969696;
                    font-size: 12px;
                    line-height: 120%;
                }
            }

            &-content {
                font-size: 14px;
                line-height: 120%;

                &-show {
                    cursor: pointer;
                    color: #007bff;
                    font-size: 14px;
                    line-height: 120%;
                }
            }
        }
    }
</style>