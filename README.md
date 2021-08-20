# apiextension
Плагин для shop script 8. Расширение апи магазина

<p>Загрузите папку в \wa-apps\shop\plugins\.<br />
В файле \wa-config\apps\shop\plugins.php пропишите «'apiextension' => true,».<br />
Сбросьте кэш, плагин установлен.</p>

<p><b>Полный список апи:</b></p>


<p>
  <b>shopApiextensionPlugin::affiliateBonus($contact_id)</b> - количество бонусов авторизованного пользователя
</p>

<p>
  <b>shopApiextensionPlugin::reviewsCount($product_ids)</b> - количество отзывов для товаров
</p>

<p>
  <b>shopApiextensionPlugin::categoryProducts($category_id, $limit)</b> - товары категории, в фильтрации товаров участвуют все гет параметры фильтра и пагинации
</p>

<p>
  <b>shopApiextensionPlugin::productImages($product_ids)</b> - фото для товаров
</p>

<p>
  <b>shopApiextensionPlugin::filtersForCategory($category_id)</b> - активный фильтр товаров для категории
</p>

<p>
<b>Дополнительные поля для отзывов</b> - в форме добавления отзыва нужно добавить поля
input c name=apiextension_experience,apiextension_dignity,apiextension_limitations,apiextension_recommend.<br />
После этого в новых отзывах будут доступные переменные
$review.apiextension_experience, $review.apiextension_dignity, $review.apiextension_limitations, $review.apiextension_recommend
</p>
