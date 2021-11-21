(function($) {
    'use sctrict'
    $.backendReviews = {
        additionalFields:{},
        delete:'',

        init: function(options) {
            this.additionalFields = options.additionalFields || {};
            this.delete = options.delete || false;

            this.initAdditionalFields();
            this.initEditFileds();
            this.initSaveFileds();
            this.initDelete();
        },
        initAdditionalFields: function() {
            let that = this;
            const aFields = that.additionalFields;
            $('.s-reviews').find('.s-review-text').hide();

            for (let id in aFields) {
                let formAdditionalFields = '<form class="apiextension-review" action="/">';

                const review = $('.s-review[data-id=' + id + ']').find('.s-review-text span').text();
                formAdditionalFields = formAdditionalFields +
                '<div class="apiextension-review__item">' +
                    '<span class=\"hint\">Отзыв:</span> ' +
                    '<span class="apiextension-review__value">' +review+ '</span>' +
                    '<span class="apiextension-review__edit"><i class="icon16 edit"></i></span>' +
                    '<div style="display:none;">' +
                        '<textarea class="apiextension-review__textarea" name="apiextension_review">' +
                            review +
                        '</textarea>' +
                        '<span class="apiextension-review__save" title="сохранить"><i class="icon16 yes"></i></span>' +
                    '</div>' +
                '</div>';

                if(aFields[id]['apiextension_experience']) {
                    formAdditionalFields = formAdditionalFields +
                    '<div class="apiextension-review__item">' +
                        '<span class=\"hint\">Опыт использования:</span> ' +
                        '<span class="apiextension-review__value">' + aFields[id]['apiextension_experience'] + '</span>' +
                        '<span class="apiextension-review__edit"><i class="icon16 edit"></i></span>' +
                        '<div style="display:none;">' +
                            '<textarea class="apiextension-review__textarea" name="apiextension_experience">' +
                                aFields[id]['apiextension_experience'] +
                            '</textarea>' +
                            '<span class="apiextension-review__save" title="сохранить"><i class="icon16 yes"></i></span>' +
                        '</div>' +
                    '</div>';
                }

                if(aFields[id]['apiextension_dignity']) {
                    formAdditionalFields = formAdditionalFields +
                    '<div class="apiextension-review__item">' +
                        '<span class=\"hint\">Достоинства:</span> ' +
                        '<span class="apiextension-review__value">' + aFields[id]['apiextension_dignity'] + '</span>' +
                        '<span class="apiextension-review__edit"><i class="icon16 edit"></i></span>' +
                        '<div style="display:none;">' +
                            '<textarea class="apiextension-review__textarea" name="apiextension_dignity">' +
                                aFields[id]['apiextension_dignity'] +
                            '</textarea>' +
                            '<span class="apiextension-review__save" title="сохранить"><i class="icon16 yes"></i></span>' +
                        '</div>' +
                    '</div>';
                }

                if(aFields[id]['apiextension_limitations']) {
                    formAdditionalFields = formAdditionalFields +
                    '<div class="apiextension-review__item">' +
                        '<span class=\"hint\">Недостатки:</span> ' +
                        '<span class="apiextension-review__value">' + aFields[id]['apiextension_limitations'] + '</span>' +
                        '<span class="apiextension-review__edit"><i class="icon16 edit"></i></span>' +
                        '<div style="display:none;">' +
                            '<textarea class="apiextension-review__textarea" name="apiextension_limitations">' +
                                aFields[id]['apiextension_limitations'] +
                            '</textarea>' +
                            '<span class="apiextension-review__save" title="сохранить"><i class="icon16 yes"></i></span>' +
                        '</div>' +
                    '</div>';
                }

                if(aFields[id]['apiextension_recommend'] && +aFields[id]['apiextension_recommend'] > 0) {
                    const recommend = aFields[id]['apiextension_recommend'] == 1
                        ? '<span class="apiextension-review__not-recommend">Не рекомендую</span>'
                        : '<span class="apiextension-review__recommend">Рекомендую</span>' ;

                    const selected1 = aFields[id]['apiextension_recommend'] == 1 ? 'selected' : '';
                    const selected2 = aFields[id]['apiextension_recommend'] == 2 ? 'selected' : '';

                    formAdditionalFields = formAdditionalFields +
                    '<div class="apiextension-review__item">' +
                        '<span class=\"hint\">Рекомендуете ли вы этот товар:</span> ' + recommend +
                        '<span class="apiextension-review__edit"><i class="icon16 edit"></i></span>' +
                        '<div style="display:none;">' +
                            '<select name="apiextension_recommend">' +
                                '<option value="2"' + selected2 + '>Рекомендую</option>' +
                                '<option value="1"' + selected1 + '>Не рекомендую</option>' +
                            '</select>' +
                            '<span class="apiextension-review__save apiextension-review__save-recommend" title="сохранить">' +
                                '<i class="icon16 yes"></i>' +
                            '</span>' +
                        '</div>' +
                    '</div>';
                }

                if(aFields[id]['apiextension_votes']) {
                    formAdditionalFields = formAdditionalFields +
                    '<div class="apiextension-review__item">' +
                    '<span class=\"hint\">Голосование:</span> за - ' + aFields[id]['apiextension_votes']['vote_like'] +
                    ', против - ' + aFields[id]['apiextension_votes']['vote_dislike'] +
                    '</div>';
                }

                formAdditionalFields = formAdditionalFields +
                '<input type="hidden" name="apiextension_review_id" value="' + id + '" />';

                formAdditionalFields = formAdditionalFields + '</form>';

                $('.s-review[data-id=' + id + ']').find('.s-review-text').after(formAdditionalFields);
            }
        },

        initEditFileds: function() {
            let that = this;
            $('.apiextension-review__edit').click(function() {
                $(this).hide().prev().hide();
                $(this).next().show();
            });
        },

        initSaveFileds: function() {
            let that = this;
            $('.apiextension-review__save').not('.apiextension-review__save-recommend').click(function() {
                const newVal = $(this).prev().val();
                $(this).parent().hide();
                $(this).parent().prev().show().prev().text(newVal).show();

                $(this).closest('.apiextension-review').submit();
            });

            $('.apiextension-review__save-recommend').click(function() {
                const valRecommend = {1:'Не рекомендую',2:'Рекомендую'};
                const newVal = $(this).prev().val();
                $(this).parent().hide();
                $(this).parent().prev().show()
                    .prev().text(valRecommend[newVal]).show()
                    .removeClass('apiextension-review__not-recommend apiextension-review__recommend')
                    .addClass(newVal == 1 ? 'apiextension-review__not-recommend' : 'apiextension-review__recommend');

                $(this).closest('.apiextension-review').submit();
            });

            $(document).on('submit', '.apiextension-review', function() {
                const f = $(this);
                $.post('?plugin=apiextension&action=reviewsEdit', f.serialize(), function (response) {
                    if (response.status == 'ok') {
                        if (response.data.error) {
                            alert(response.data.error)
                        }
                    } else if (response.status == 'fail') {
                        alert(response.errors);
                    }
                }, "json");

                return false;
            });
        },

        initDelete: function() {
            let that = this;
            if (that.delete) {
                $('.s-review-delete').click(function(e) {
                    e.preventDefault();
                    const that = $(this);
                    if (confirm("Действительно удалить?")) {
                        console.log('APIEXTENSION - START DELETE REVIEW');
                        setTimeout(()=>{
                            $(this).closest('li').remove();
                        }, 100);
                    } else {
                        console.log('APIEXTENSION - STOP DELETE REVIEW');
                        e.stopPropagation();
                    }
                });
            }
        },
    }
})(jQuery);