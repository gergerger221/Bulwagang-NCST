<?php
// Footer component with page-specific scripts
$js_files = getPageJS($current_page ?? '');
?>

<!-- Page-specific JavaScript -->
<?php foreach ($js_files as $js_file): ?>
<script src="<?php echo $js_file; ?>"></script>
<?php endforeach; ?>

<?php if (isset($additional_js)): ?>
<!-- Additional JavaScript -->
<?php foreach ($additional_js as $js): ?>
<script src="<?php echo $js; ?>"></script>
<?php endforeach; ?>
<?php endif; ?>

</body>
</html>
