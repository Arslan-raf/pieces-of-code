@props([
    'isRegister' => false,
    'parent'=>null,
    'house'=>null,
    'address'=>null,
    'forCreateBidBySchool'=>false,
    'readOnly' => false,
    'parentCitizenship'=>null,
])
@php
    use App\Models\Citizenship;
    $isRuCitizenship = !isset($parent->citizenship) || Citizenship::RU_PASSPORT == $parent->citizenship;
    $isForeignCitizenship = isset($parent->citizenship) && Citizenship::EN_PASSPORT == $parent->citizenship;
@endphp

<div class="modal-body">
    @if ($readOnly)
        <div class="row mb-2">
            <div class="col-sm">
                <span class="control-label text-primary">(Так как есть принятые заявления, редактирование запрещено)</span>
            </div>
        </div>
    @endif
<div class="row">
    <div class="col-sm-3">
        <div class="form-group">
            <label class="control-label required" for="parent_l_name">Фамилия {{$parent->parent_id ?? ''}}<span class="text-danger">*</span></label>
            @if (isset($parent->parent_id))
                <input type="hidden" name="parent_id" value="{{$parent->parent_id ?? ''}}">
            @endif
            <input class="form-control @error('parent_l_name') is-invalid @enderror" type="text" value="{{ old('parent_l_name') ?? $parent->l_name ?? ''  }}" name="parent_l_name" id="parent_l_name" required autofocus {{ $readOnly ? 'readonly' : '' }}>
            @error('parent_l_name')
                <span class="invalid-feedback" role="alert">
                    <strong>{{ $message }}</strong>
                </span>
            @enderror
        </div>
    </div>

    <div class="col-sm-3">
        <div class="form-group">
            <label class="control-label required" for="parent_f_name">Имя<span class="text-danger">*</span></label>
            <input class="form-control" type="text" value="{{ old('parent_f_name') ?? $parent->f_name ?? ''}}" name="parent_f_name" id="parent_f_name" required {{ $readOnly ? 'readonly' : '' }}>

        </div>
    </div>

    <div class="col-sm-3">
        <div class="form-group">
            <label class="control-label" for="parent_m_name">Отчество</label>
            <input class="form-control" type="text" value="{{ old('parent_m_name') ?? $parent->m_name ?? '' }}" name="parent_m_name" id="parent_m_name" {{ $readOnly ? 'readonly' : '' }}>
        </div>
    </div>

    <div class="col-sm-3">
        <div class="form-group">
            <label class="control-label" for="parent_category">Тип представительства<span class="text-danger">*</span></label>
            <select class="form-control" name="parent_category" id="parent_category" required {{ $readOnly ? 'disabled' : '' }}>
                <option value="" selected="" disabled="">Выберите</option>
                <option {{ (isset($parent) && $parent->category == "Законный представитель") ? 'selected' : '' }}
                     value="Законный представитель">Законный представитель</option>
                <option {{ (isset($parent) && $parent->category == "Мать") ? 'selected' : '' }}
                     value="Мать">Мать</option>
                <option {{ (isset($parent) && $parent->category == "Отец") ? 'selected' : '' }} value="Отец">Отец</option>
            </select>
        </div>
    </div>

</div>
<h4>Адрес места жительства</h4>
<div class="row">
    <div class="col-md-8">
        <label>Населенный пункт, улица<span class="text-danger">*</span></label>
        <select id="parent_street_reg" style="width: 100%" class="form-control" required {{ $readOnly ? 'disabled' : '' }}>
            <option value="{{$address->id ?? ''}}" title=" {{$address->title ?? ''}}" selected></option>
        </select>
    </div>
    <div class="col-md-2">
        <label>Дом<span class="text-danger">*</span></label>
        <select id="parent_house_reg" style="width: 100%"  class="form-control"  name="parent_fias_house_id" required
        {{ $readOnly ? 'disabled' : '' }}>
            <option value="{{$house->id ?? ''}}" title="{{$house->title ?? ''}}" selected></option>
        </select>
    </div>
    <div class="col-md-2">
        <label>Квартира</label>
        <input placeholder="Номер квартиры" value="{{ old('parent_flat') ?? $parent->flat ?? ''}}" class="form-control" type="text" name="parent_flat" id="parent_flat" {{ $readOnly ? 'readonly' : '' }}>
    </div>
</div>
@if (!$readOnly)
    <abbr title="Добавить адрес в систему" id='UserAddHouse' class="small">Если не нашли адрес своего дома, нажмите чтобы добавить</abbr>
@endif
<hr>
<h4>Документы</h4>

<div class="row">
    <div class="col-sm-3 col-lg-3" >
        <div class="form-group">
            <label class="control-label required" for="parent_citizenship">Гражданство<span class="text-danger">*</span></label>
            <select class="form-control" name="parent_citizenship" id="parent_citizenship" required {{ $readOnly ? 'disabled' : '' }}>
                @foreach ( $parentCitizenship as $id => $cit)
                    <option
                    @if (isset($parent) && $parent->citizenship == $id)
                        selected
                    @endif
                            value="{{ $id }}">
                            {{ $cit }}
                        </option>
                @endforeach
            </select>
        </div>
    </div>

    <div class="col-md-2 col-lg-2 document1_for_parent" @if ($isForeignCitizenship) style="display: none" @endif>
        <div class="form-group"><label class="control-label required" for="parent_doc_seria">Серия паспорта<span class="text-danger">*</span></label>
            <input class="form-control" type="text" value="{{ old('parent_doc_seria') ?? $parent->doc_seria ?? '' }}" name="parent_doc_seria" id="parent_doc_seria" required="" maxlength="4" pattern="[0-9]{4}"
            {{ $readOnly ? 'readonly' : '' }}>
        </div>
    </div>

    <div class="col-md-2 col-lg-2 document2_for_parent">
        <div class="form-group">
            <label class="control-label required" for="parent_doc_number">Номер паспорта <span class="text-danger">*</span></label>
            <input class="form-control"  value="{{ old('parent_doc_number') ?? $parent->doc_number ?? ''}}" type="text" name="parent_doc_number" id="parent_doc_number"  required="" maxlength="6" pattern="[0-9]{6}"
            {{ $readOnly ? 'readonly' : '' }}>
        </div>
    </div>

    <div class="col-md-3 col-lg-3 document2_for_parent">
        <div class="form-group">
            <label class="control-label required" for="parent_doc_created_at">Дата выдачи <span class="text-danger">*</span></label>
            <div class="rails-bootstrap-forms-date-select">
                <input type="date" name="parent_doc_created_at" value="{{ old('parent_doc_created_at') ?? $parent->doc_created_at ?? ''}}" pattern="[0-9]{4}-[0-9]{2}-[0-9]{2}" max="{{date("Y-m-d")}}" class="form-control" required=""
                {{ $readOnly ? 'readonly' : '' }}>
            </div>
        </div>
    </div>

</div>
<hr>
<h4>Контакты</h4>
<div class="row">
    <div class="col-md-3 col-lg-3">
        <div class="form-group">
            <label class="control-label required" for="parent_phone" >Телефон <span class="text-danger">*</span></label>
            <input class="form-control" type="text" value="{{ old('parent_phone') ?? $parent->phone ?? ''}}" name="parent_phone" id="parent_phone" required="" {{ $readOnly ? 'readonly' : '' }} >
        </div>
    </div>

    <div class="col-md-3 col-lg-3">
        <div class="form-group">
            <label class="control-label required" for="parent_additional_contac">Дополнительный контакт</label>
            <input class="form-control" type="text" value="{{ old('parent_additional_contact') ?? $parent->additional_contact ?? '' }}" name="parent_additional_contact" id="parent_additional_contac" {{ $readOnly ? 'readonly' : '' }}>
        </div>
    </div>
    @if ($isRegister)
    <div class="col-md-6 col-lg-6">
        <div class="form-group">
            <label class="control-label" for="email">E-mail <span class="text-danger">* </span><span class="text-muted">( Логин )</span></label>
            <input class="form-control @error('email') is-invalid @enderror" type="email" value="{{ old('email') }}" name="email" id="email" required autocomplete="email">
            @error('email')
            <span class="invalid-feedback" role="alert">
                <strong>{{ $message }}</strong>
            </span>
            @enderror
        </div>
    </div>
    @endif

@if ( !$isRegister && !$forCreateBidBySchool )
    <div class="col-md-4 col-lg-4">
        <button type="button" class="btn btn-outline-primary log-change" style="margin-top: 1.97rem !important">Сменить почту(логин)</button>
    </div>
@endif
</div>
    @if ($isRegister)
        <div class="row">
            <div class="col-sm-6">
                <div class="form-group">
                    <label class="control-label" for="password">Пароль<span class="text-danger">*</span></label>
                    <input id="password" type="password" class="form-control @error('password') is-invalid @enderror" name="password" required minlength="8">
                    @error('password')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                </div>
            </div>
            <div class="col-sm-6">
                <div class="form-group">
                    <label class="control-label" for="password-confirm">Повторите пароль <span class="text-danger">*</span></label>
                    <input id="password-confirm" type="password" class="form-control" name="password_confirmation" required minlength="8">
                </div>
            </div>
        </div>
            <div class="custom-control custom-checkbox my-4">
                <input type="hidden" name="edit_agree" value="0">
                <input type="checkbox" class="custom-control-input" id="agree" required="" name="agree" value="1">
                <label class="custom-control-label" for="agree">Даю согласие на обработку <a href="#" title="Прочитать" id="agreement">персональных данных</a></label>
            </div>
    @endif
</div>
<script src="{{ asset('js/jquery.mask.js') }}"></script>
<script>
$(function() {

    $('#parent_l_name').mask('C', {
            translation: {
                'C': {
                    pattern: /[A-Za-zА А-Яа-яЁё \- \s]+/,
                    recursive: true
                }
            }
        });

        $('#parent_f_name').mask('C', {
            translation: {
                'C': {
                    pattern: /[A-Za-zА А-Яа-яЁё \-\s]+/,
                    recursive: true
                }
            }
        });
        $('#parent_m_name').mask('C', {
            translation: {
                'C': {
                    pattern: /[A-Za-zА А-Яа-яЁё \-\s]+/,
                    recursive: true
                }
            }
        });

        $('#UserAddHouse').on('click', function (event) {
            event.preventDefault();
            $('#modalAjax').load("/layouts/UserAddHouse");
        });


        $('#parent_citizenship').on('change', function() {
         if ($('#parent_citizenship').val() == '4') { //иностранный паспорт
            $(".document1_for_parent").hide();
            $(".document2_for_parent").show();
            $(".document2_for_parent [name='parent_doc_number']").css("border-color",'#ced4da');
            $(".document2_for_parent [name='parent_doc_number']").parent().find('.error_input').remove();
            $(".document1_for_parent [name='parent_doc_seria']").removeAttr("required maxlength pattern minlength");
            $(".document2_for_parent [name='parent_doc_number']").removeAttr("maxlength pattern");
            $(".document2_for_parent [name='parent_doc_number'], [name='parent_doc_created_at']").attr("required", 'true');

            $(".document1_for_parent [name='parent_doc_seria']").parent().find('.error_input').remove();
            $(".document1_for_parent [name='parent_doc_seria']").css("border-color",'#ced4da');
        }
        else if ($('#parent_citizenship').val() == '3'){ //РФ паспорт
            $(".document1_for_parent [name='parent_doc_seria']").attr("required", 'true');
            $(".document2_for_parent [name='parent_doc_number'], [name='parent_doc_created_at']").attr("required", 'true');
            $(".document1_for_parent [name='parent_doc_seria']").attr("maxlength", "4");
            $(".document2_for_parent [name='parent_doc_number']").attr("maxlength", "6");
            $(".document1_for_parent [name='parent_doc_seria']").attr("minlength", "4");
            $(".document2_for_parent [name='parent_doc_number']").attr("minlength", "6");
            $(".document1_for_parent [name='parent_doc_seria']").attr("pattern", "[0-9]{4}");
            $(".document2_for_parent [name='parent_doc_number']").attr("pattern", "[0-9]{6}");
            $(".document1_for_parent").show();
            $(".document2_for_parent").show();

            if( !isNaN($('[name="parent_doc_number"]').val()) && !($('[name="parent_doc_number"]').val().length > 6)){
                $('[name="parent_doc_number"]').parent().find('.error_input').remove();
                $('[name="parent_doc_number"]').css("border-color",'#ced4da');
            }else {
                if( !($('#parent_citizenship').val() == '4')){
                    $('[name="parent_doc_number"]').css("border-color","red");
                    if ( !$('[name="parent_doc_number"]').parent().find('.error_input').length){
                        $('[name="parent_doc_number"]').parent().append('<div class="text-danger error_input">вводите пожалуйста только 6 чисел </div>');
                    }
                }
            }

            if( !isNaN($('[name="parent_doc_seria"]').val()) && !($('[name="parent_doc_seria"]').val().length > 4)){
                $('[name="parent_doc_seria"]').parent().find('.error_input').remove();
                $('[name="parent_doc_seria"]').css("border-color",'#ced4da');
            }else {
                if( !($('#parent_citizenship').val() == '4')){
                    $('[name="parent_doc_seria"]').css("border-color","red");
                    if ( !$('[name="parent_doc_seria"]').parent().find('.error_input').length){
                        $('[name="parent_doc_seria"]').parent().append('<div class="text-danger error_input">вводите пожалуйста только 4 числа </div>');
                    }
                }
            }
        };
    })

    $('#parent_doc_number ,#parent_doc_seria').on('input',function(){
            var value = $(this).val();
            if(!isNaN(value)){
                $(this).parent().find('.error_input').remove();
                $(this).css("border-color",'#ced4da');
            }else {
                if( !($('#parent_citizenship').val() == '4')){
                    $(this).css("border-color","red");
                    if ( !$(this).parent().find('.error_input').length){
                        $(this).parent().append('<div class="text-danger error_input">вводите пожалуйста только числа</div>');
                    }
                }
            }
        });

        $('#parent_l_name , #parent_f_name, #parent_m_name').on('input',function(){
            var value = $(this).cleanVal();
            if((/([a-zA-Z]+)/.test(value))){
                $(this).css("border-color","red");
                if ( !$(this).parent().find('.error_input').length){
                    $(this).parent().append('<div class="text-danger error_input">вводите пожалуйста только кириллицу</div>');
                }
            }else {
                $(this).parent().find('.error_input').remove();
                $(this).css("border-color",'#ced4da');
            }
        });

    $('#parent_street_reg').select2({
            ajax:{
                url: '/address_new',
                dataType:'json',
                delay: 250,
                data: function (params) {
                    return{
                        q: params.term,
                        page: params.page
                    };
                },
                processResults: function(data, params){
                    params.page = params.page || 1;
                    return {
                        results: data.data,
                        pagination: {
                            more: (params.page*10)<data.total
                        }
                    };
                }
            },
            minimumInputLength: 2,
            templateResult: function(repo){
                return repo.title;
            },
            templateSelection: function(repo){
                return  repo.title;
            },
        });

        $('#parent_street_reg').on('change', function() {
            $("#parent_house_reg").empty().trigger('change')
        });

        $('#parent_house_reg').select2({
            ajax:{
                url: '/house_new',
                dataType: 'json',
                type: 'GET',
                data: function (params) {
                    $fias_address_object_id = $('#parent_street_reg').val();
                    return {
                        q: params.term,
                        id: $fias_address_object_id,
                        page: params.page
                    };
                },
                processResults: function(data, params){
                    params.page = params.page || 1;
                    return {
                        results: data,
                        pagination: {
                            more: (params.page*10)<data.total
                        }
                    };
                }
            },
            templateResult: function(repo){
                return repo.title;
            },
            templateSelection: function(repo){
                return  repo.title;
            }
        });

        $('.log-change').on('click', function (event) {
            if (confirm('Это действие переведет вас на другую страницу. Вы уверены что хотите перейти?'))
            {
                window.location.href = "/profile"
            }
        });
})
</script>
