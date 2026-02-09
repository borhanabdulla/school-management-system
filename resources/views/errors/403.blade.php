@section('title', __('غير مصرح لك'))
@section('code', '403')
@section('message', __($exception->getMessage() ?: 'ليس لديك صلاحية للوصول إلى هذه الصفحة.'))
