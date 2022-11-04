@props([
    'child' => null,
    'privileges' => $privileges,
    'readOnly' => false,
    'forEdit' => false,
    'grades' => null,
    'forCreateBidBySchool' => false,
    'bid'=>null,
    'father'=>false,
    'citizenship'=>null,
])

@php
    use App\Models\Citizenship;

    $isRuCitizenship = isset($child->citizenship) && Citizenship::RU_CERTIFICATE == $child->citizenship;
    $isForeignCitizenship = isset($child->citizenship) && Citizenship::EN_CERTIFICATE == $child->citizenship;

    $isRuPassport = isset($child->citizenship) && Citizenship::RU_PASSPORT == $child->citizenship;
    $isForeignPassport = isset($child->citizenship) && Citizenship::EN_PASSPORT == $child->citizenship;

    $readOnlyOrForEdit = $forEdit || $readOnly;
    $year =  isset($child) ? ($child->getAge()['y']) : null ;
    $month =  isset($child) ? ($child->getAge()['m']) : null ;
    // dd($child->citizenship);
@endphp

<div class="modal-header">
    <h5 class="modal-title" id="exampleModalLabel">Данные ребёнка <span class="text-muted">(Предоставление неверных
            данных является причиной отказа заявлений)</span>
    </h5>
    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
        <span aria-hidden="true">&times;</span>
    </button>
</div>
<div class="modal-body" id="child-data-form">
    @if ($readOnly)
        <div class="row mb-2">
            <div class="col-sm">
                <span class="control-label text-primary">(Так как заявление принято, редактирование запрещено)</span>
            </div>
        </div>
    @endif
    <div class="row">
        <div class="col-md-3">
            <div class="form-group">
                <label>Фамилия <span class="text-danger">*</span></label>
                <input type="text" id="l_name" name="l_name" class="form-control l_name" required=""
                    {{ $readOnly ? 'readonly' : '' }} value="{{ $child->l_name ?? '' }}">
            </div>
        </div>
        <div class="col-md-3">
            <div class="form-group">
                <label>Имя <span class="text-danger">*</span></label>
                <input type="text" id="f_name" name="f_name" class="form-control f_name" required=""
                    {{ $readOnly ? 'readonly' : '' }} value="{{ $child->f_name ?? '' }}">
            </div>
        </div>
        <div class="col-md-3">
            {{-- <div class="custom-control custom-checkbox"> --}}
                @if (!$father)
                    <input type="hidden" name="" value="0">
                    <input type="checkbox" class="custom-checkbox" id="m_name_check" name="m_name_check"
                    {{ $readOnly ? 'disabled' : '' }}>
                @endif
                <label id="m_name_required" for="m_name_check">Отчество @if (!$father) (нет отчества)<span class="text-danger">*</span> @endif </label>
                <input type="text" id="m_name" name="m_name" class="form-control m_name"   @if (!$father)  required="" @endif
                    {{ $readOnly ? 'readonly' : '' }} value="{{ $child->m_name ?? '' }}">
            {{-- </div> --}}
        </div>
        <div class="col-md-3">
            <div class="form-group">
                <label>Пол <span class="text-danger">*</span></label>
                <select name="gender" class="form-control" {{ $readOnly ? 'disabled' : '' }}>
                    <option value="Мужской" @if (isset($child->gender) && 'Мужской' == $child->gender) selected @endif>Мужской
                    </option>
                    <option value="Женский" @if (isset($child->gender) && 'Женский' == $child->gender) selected @endif>Женский
                    </option>
                </select>
            </div>
        </div>
    </div>

    {{-- Для создания заявления --}}
    @if ($forCreateBidBySchool)
        <div class="row">
            <div class="col-md-3">
                <div class="form-group">
                    <label>Класс этой школы <span class="text-danger">*</span></label>
                    <select title="Если нет нужного, добавьте в разделе классы" class="form-control" name="grade_number"  required {{ $readOnly ? 'disabled' : '' }}>
                        <option  value="" selected="" disabled="">Выберите</option>
                        @if (isset($bid->grade_number)) <option  selected value="{{$bid->grade_number}}">
                            {{$bid->grade_number}}</option> @endif
                        @foreach($grades as $grade)
                            <option value="{{$grade->number}}">{{$grade->number}}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="col-md-3">
                <label>Желаемый язык обучения:</label>
                <select name="language" class="form-control" required {{ $readOnly ? 'disabled' : '' }}>
                    @if (isset($bid->language)) <option  selected value="{{$bid->language}}">
                        {{$bid->language}}</option> @endif
                    <option value="Русский">Русский</option>
                    <option value="Башкирский">Башкирский</option>
                    <option value="Татарский">Татарский</option>
                    <option value="Чувашский">Чувашский</option>
                    <option value="Марийский">Марийский</option>
                    <option value="Удмуртский">Удмуртский</option>
                </select>
            </div>
        </div>
    @endif

    <div class="row">
        <div class="col-md-3">
            <label>Дата рождения <span class="text-danger">*</span> </label>
            <input class="form-control birth" type="date" name="born_at" pattern="[0-9]{4}-[0-9]{2}-[0-9]{2}"
                max="{{ date('Y-m-d') }}" required {{ $readOnly ? 'readonly' : '' }}
                value="{{ $child->born_at ?? '' }}">
        </div>

        <div class="col-md-3">
            <div class="form-group">
                <label id="citizenship_label">Тип документа <span class="text-danger">*</span></label>
                <select id="citizenship" class="form-control" name="citizenship" class="form-control" required id="foreign"
                    {{ $readOnly ? 'disabled' : '' }}>
                    <option id="" value="" selected="" disabled="">Введите сначала дату рождения</option>
                    @foreach ( $citizenship as $id => $cit)
                        <option
                            @if(
                                isset($child) &&
                                $child->citizenship == $id
                                &&
                                !(
                                    $child->citizenship == Citizenship::RU_CERTIFICATE &&
                                    (($child->getAge()['y'] >= 14 && $child->getAge()['m'] > 3) || $child->getAge()['y'] > 14 )
                                )
                            )
                                selected
                            @else
                                hidden
                            @endif
                            id="child_option_{{$id}}"  value="{{ $id }}">
                                {{ $cit }}
                            </option>
                    @endforeach
                </select>
            </div>
        </div>
        <div class="col-md-3">
            <label>Место рождения:</label>
            <input class="form-control" type="text" name="born_place" {{ $readOnly ? 'readonly' : '' }}
                value="{{ $child->born_place ?? '' }}">
        </div>


        <div class="col-md-3">
            <label>Возраст: </label>
            <p class="age"></p>
        </div>
    </div>

    <div class="row certificate" style="display: none">
        {{-- @if (!($year >=14 && $month>=3)) style="display: none" @endif --}}
        <div class="col-md-3 document1" @if ($isForeignCitizenship)  style="display: none" @endif>
            <div class="form-group">
                <label id="birth_certificate_seria_a_label">Серия свидетельства <span
                        class="text-danger">*</span></label>
                <div class="input-group">
                    <select class="form-control" name="birth_certificate_seria_a" class="form-control"
                        {{-- @if ($isRuCitizenship) required="" @endif --}}
                        id="birth_certificate_seria_a" {{ $readOnly ? 'disabled' : '' }}>
                        <option value="" selected="" disabled="">Выберите</option>
                        <option value="I" @if (isset($child->birth_certificate_seria_a) && 'I' == $child->birth_certificate_seria_a) selected @endif>I</option>
                        <option value="II" @if (isset($child->birth_certificate_seria_a) && 'II' == $child->birth_certificate_seria_a) selected @endif>II</option>
                        <option value="III" @if (isset($child->birth_certificate_seria_a) && 'III' == $child->birth_certificate_seria_a) selected @endif>III</option>
                        <option value="IV" @if (isset($child->birth_certificate_seria_a) && 'IV' == $child->birth_certificate_seria_a) selected @endif>IV</option>
                        <option value="V" @if (isset($child->birth_certificate_seria_a) && 'V' == $child->birth_certificate_seria_a) selected @endif>V</option>
                        <option value="VI" @if (isset($child->birth_certificate_seria_a) && 'VI' == $child->birth_certificate_seria_a) selected @endif>VI</option>
                        <option value="VII" @if (isset($child->birth_certificate_seria_a) && 'VII' == $child->birth_certificate_seria_a) selected @endif>VII</option>
                        <option value="VIII" @if (isset($child->birth_certificate_seria_a) && 'VIII' == $child->birth_certificate_seria_a) selected @endif>VIII
                        </option>
                        <option value="IX" @if (isset($child->birth_certificate_seria_a) && 'IX' == $child->birth_certificate_seria_a) selected @endif>IX</option>
                        <option value="X" @if (isset($child->birth_certificate_seria_a) && 'X' == $child->birth_certificate_seria_a) selected @endif>X</option>
                        <option value="XI" @if (isset($child->birth_certificate_seria_a) && 'XI' == $child->birth_certificate_seria_a) selected @endif>XI</option>
                        <option value="XII" @if (isset($child->birth_certificate_seria_a) && 'XII' == $child->birth_certificate_seria_a) selected @endif>XII</option>
                    </select>
                    <span class="input-group-append"><span class="input-group-text">—</span></span>
                    <input type="text" id="birth_certificate_seria_b" placeholder="БЖ" name="birth_certificate_seria_b"
                        class="form-control birth_certificate_seria_b"
                        {{-- @if ($isRuCitizenship) required="" pattern="[А-Яа-яЁё]{2}"  @endif --}}
                         maxlength="2" minlength="2"
                        {{ $readOnly ? 'readonly' : '' }}
                        @if (isset($child->birth_certificate_seria_b)) value="{{ $child->birth_certificate_seria_b }}" @endif>
                </div>
            </div>
        </div>
        <div class="col-md-3 document2">
            <div class="form-group">
                <label id="birth_certificate_number_label">Номер свидетельства <span
                        class="text-danger">*</span></label>
                <input type="text" name="birth_certificate_number" id="birth_certificate_number"
                    class="form-control birth_certificate_number" minlength="6" maxlength="6"
                    {{ $readOnly ? 'readonly' : '' }}
                    value="{{ $child->birth_certificate_number ?? '' }}">
            </div>
        </div>
        <div class="col-md-3 document2">
            <div class="form-group">
                <label id="birth_certificate_created_at_label">Дата выдачи свидетельства <span
                        class="text-danger">*</span></label>
                <input type="date" name="birth_certificate_created_at" class="form-control birth_certificate_created_at"
                    pattern="[0-9]{4}-[0-9]{2}-[0-9]{2}" max="{{ date('Y-m-d') }}"
                    id="birth_certificate_created_at" {{ $readOnly ? 'readonly' : '' }}
                    value="{{ $child->birth_certificate_created_at ?? '' }}">
            </div>
        </div>
    </div>

    {{-- Паспортные данные --}}
    <div class="row passport" style="display: none" @if (!($year >=14 && $month>=3)) style="display: none" @endif>
        <div class="col-md-3">
            <div class="form-group">
                <label id="doc_created_at_label">Дата выдачи</label>
                <input type="date" name="doc_created_at" class="form-control doc_created_at"
                    pattern="[0-9]{4}-[0-9]{2}-[0-9]{2}" max="{{ date('Y-m-d') }}" id="doc_created_at"
                    {{ $readOnly ? 'readonly' : '' }} value="{{ $child->doc_created_at ?? '' }}"
                    {{-- @if (!($year >=14 && $month>=3)) readonly @endif --}}
                    >
            </div>
        </div>
        <div class="col-md-2 passport_seria" @if($isForeignPassport) style="display: none" @endif>
            <div class="form-group">
                <label id="doc_seria_label">Серия документа</label>
                <input type="text" name="doc_seria" class="form-control doc_seria" id="doc_seria" minlength="4" maxlength="4"
                    {{ $readOnly ? 'readonly' : '' }} value="{{ $child->doc_seria ?? '' }}"
                    {{-- @if (!($year >=14 && $month>=3)) readonly @endif --}}
                    >
            </div>
        </div>
        <div class="col-md-2">
            <div class="form-group">
                <label id="doc_number_label">Номер документа</label>
                <input type="text" id="doc_number" name="doc_number" class="form-control doc_number" minlength="6" maxlength="6"
                    {{ $readOnly ? 'readonly' : '' }} value="{{ $child->doc_number ?? '' }}"
                    {{-- @if (!($year >=14 && $month>=3)) readonly @endif--}}
                        >
            </div>
        </div>
        <div class="col-md-2 passport_snils">
            <div class="form-group">
                <label id="snils_label">СНИЛС</label>
                <input id="snils" type="text" name="snils" class="form-control snils"
                    {{ $readOnly ? 'readonly' : '' }} value="{{ $child->snils ?? '' }}"
                   {{-- @if (!($year >=14 && $month>=3)) readonly @endif--}}
                    >
            </div>
        </div>
    </div>


    <div class="row">
        <div class="col-md-8">
            <label>Фактический адрес: населенный пункт, улица<span
                    class="text-danger">*</span></label>
            <select id="street_fact" style="width: 100%" class="form-control" required="" name="street_fact"
                {{ $readOnly ? 'disabled' : '' }}>
                <option value="{{ $child->fiasFactHouse->id ?? '' }}"
                    title=" {{ $child->fiasFactHouse->fiasObject->full_address  ?? '' }}" selected></option>
            </select>
        </div>
        <div class="col-md-2">
            <label>Дом <span class="text-danger">*</span></label>
            <select id="house_fact" name="fias_fact_house_id" style="width: 100%" class="form-control" required=""
                {{ $readOnly ? 'disabled' : '' }}>
                <option value="{{ $child->fiasFactHouse->id ?? '' }}"
                    title=" {{ $child->fiasFactHouse->housenum ?? '' }}" selected></option>
            </select>
        </div>
        <div class="col-md-2">
            <label>Квартира</label>
            <input type="text" name="fact_flat" class="form-control fact_flat" {{ $readOnly ? 'readonly' : '' }}
                value="{{ $child->fact_flat ?? '' }}">

        </div>

    </div>
    <div class="d-flex justify-content-between align-items-center">
        <div class="custom-control custom-checkbox my-4">
            <input type="hidden" name="address_equal" value="0">
            <input type="checkbox" class="custom-control-input" id="address_equal" name="address_equal"
                @if (isset($child) && $child->address_equal) checked @endif {{ $readOnly ? 'disabled' : '' }}>
            <label class="custom-control-label" for="address_equal">Адрес регистрации совпадает с
                фактическим адресом</label>
        </div>
        @if (!$readOnly)
            <abbr title="Добавить адрес в систему" id='UserAddHouse' class="small">Если не нашли
                адрес своего дома, нажмите чтобы добавить</abbr>
        @endif
    </div>


    <div class="row address_fact" @if (isset($child) && $child->address_equal==1) style="display: none;" @endif>
        <div class="col-md-8">
            <label>1. Адрес регистрации: населенный пункт, улица {{ $child->fiasHouse->id ?? '' }}
                {{ $child->fiasHouse->full_address ?? '' }} </label>
            <select id="street_reg" style="width: 100%" class="form-control" {{ $readOnly ? 'disabled' : '' }}
                name="street_reg">
                <option value=" {{ $child->fiasHouse->id ?? '' }}"
                    title=" {{ $child->fiasHouse->fiasObject->full_address ?? '' }}" selected></option>
            </select>
        </div>
        <div class="col-md-2">
            <label>2. Дом </label>
            <select id="house_reg" style="width: 100%" class="form-control" name="fias_house_id"
                {{ $readOnly ? 'disabled' : '' }}>
                <option value=" {{ $child->fiasHouse->id ?? '' }}"
                    title=" {{ $child->fiasHouse->full_number ?? '' }}" selected></option>
            </select>
        </div>
        <div class="col-md-2">
            <label>Квартира</label>
            <input type="text" value="{{ $child->flat ?? '' }}" class="form-control flat" name="flat"
                {{ $readOnly ? 'disabled' : '' }}>
        </div>
    </div>
    <hr>
    <div class="form-group">
        <h4>Категория льготы</h4>
        <select id="child_category" class="form-control" name="child_category_id" class="form-control" required
            {{ $readOnly ? 'disabled' : '' }}>
            @foreach ($privileges as $privilege)
                <option value="{{ $privilege->id }}" @if (isset($child->child_category_id) && $privilege->id == $child->child_category_id) selected @endif>
                    {{ $privilege->title }}
                </option>
            @endforeach
        </select>
    </div>


    {{-- для подачи заявлений школами загрузку пока убрать --}}
    @if (!$forCreateBidBySchool)
    <hr>
    <h4>Сканы документов <span class="text-danger">*</span></h4>

    <div class="row">
        <div class="col-md-3">
            <div class="form-group">
                @if ($forEdit || $readOnly) <p class="registration_doc_scan"><a download="" href="#"><i class="fas fa-file-download"></i>
                        Скачать</a></p> @endif
                <label>Скан-фото справки о регистрации по месту жительства (форма №8) или по месту
                    пребывания(форма №3), либо скан-фото паспорта ребенка страницы с пропиской<span class="text-danger">*</span></label>
                    @if (!$readOnly) <input type="file" name="registration_doc_scan" class="form-control-file"
                    id="registration_doc_scan" required >@endif
            </div>
        </div>

        <div class="col-md-3">
            <div class="form-group">
                @if ($forEdit || $readOnly) <p class="birth_certificate_scan"><a download="" href="#"><i class="fas fa-file-download"></i>
                        Скачать</a></p> @endif
                <label>Скан-фото свидетельства о рождении ребенка, либо скан-фото паспорта ребенка
                    первой страницы с фото <span class="text-danger">*</span></label>
                    @if (!$readOnly) <input type="file" id="birth_certificate_scan" name="birth_certificate_scan" class="form-control-file" required>@endif
            </div>
        </div>

        <div class="col-md-2">
            <div class="form-group">
                @if ($forEdit || $readOnly) <p class="parent_passport_scan"><a download="" href="#"><i class="fas fa-file-download"></i>
                        Скачать</a></p> @endif
                <label>Скан-фото паспорта родителя<span class="text-danger">*</span></label>
                @if (!$readOnly) <input type="file" id="parent_passport_scan" name="parent_passport_scan" class="form-control-file" required> @endif
            </div>
        </div>


            <div class="col-md-2 @if (!isset($child) || $child->categories->notes == 'Без льготы') d-none @endif benefits" >
                <div class="form-group">
                @if ($forEdit || $readOnly) <p class="child_categories_scan"><a download="" href="#"><i class="fas fa-file-download"></i>
                            Скачать</a></p> @endif
                    <label>Скан-фото документа подтверждающего льготу<span class="text-danger">*</span></label>
                    @if (!$readOnly)  <input type="file" name="child_categories_scan" id="child_categories_scan" class="form-control-file">@endif
                </div>
            </div>

        <div class="col-md-2">
            <div id="older_child_scan" class="form-group">
                @if ($forEdit || $readOnly) <p class="older_child_scan"><a download="" href="#"><i class="fas fa-file-download"></i>
                        Скачать</a></p> @endif
                <label>Скан-фото документа подтверждающего преимущественоое право <i
                        class="fas fa-user-friends text-primary" title="" data-toggle="tooltip"
                        data-original-title="Старшие дети обучаются в этой школе"></i></label>
                @if (!$readOnly) <input type="file" name="older_child_scan" class="form-control-file">@endif
            </div>
        </div>
    </div>
    @endif
</div>

<link rel="stylesheet" href="{{ asset('css/select2.min.css') }}">
<script src="{{ asset('js/file-validator.js') }}"></script>
<script src="{{ asset('js/select2.min.js') }}"></script>
<script src="{{ asset('js/jquery.mask.js') }}"></script>
<script>
    (function() {

        $('#snils').mask('000-000-000 00');
        $('#l_name').mask('C', {
            translation: {
                'C': {
                    pattern: /[A-Za-zА А-Яа-яЁё \- \s]+/,
                    recursive: true
                }
            }
        });

        $('#f_name').mask('C', {
            translation: {
                'C': {
                    pattern: /[A-Za-zА А-Яа-яЁё \-\s]+/,
                    recursive: true
                }
            }
        });
        $('#m_name').mask('C', {
            translation: {
                'C': {
                    pattern: /[A-Za-zА А-Яа-яЁё \-\s]+/,
                    recursive: true
                }
            }
        });
        $('#birth_certificate_seria_b').mask('C', {
            translation: {
                'C': {
                    pattern: /[A-Za-zА А-Яа-яЁё]+/,
                    recursive: true
                }
            }
        });

        @if ($readOnly || $forEdit)
            let docs = ['registration_doc_scan', 'birth_certificate_scan',
            'parent_passport_scan','child_categories_scan','older_child_scan'];

            $.ajax ({
            headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
            url:"{{ route('ModalChild') }}",
            type:"POST",
            dataType:'json',
            data:"child="+{{ $_POST['id'] }},
            success:function(html){
                for (let i = 0; i < docs.length; i++) {
                        if (html[docs[i]]){
                            $('#childdata .' + docs[i] + ' a' ).attr('href', html[docs[i]]);
                            $('#' + docs[i]).removeAttr('required');
                        }
                        else {
                            $('#childdata .' + docs[i]).html('—');
                            }
                    }
                }
            });

        @endif


        $('#street_reg').select2({
            ajax: {
                url: '/address_new',
                dataType: 'json',
                delay: 250,
                data: function(params) {
                    return {
                        q: params.term,
                        page: params.page
                    };
                },
                processResults: function(data, params) {
                    params.page = params.page || 1;
                    return {
                        results: data.data,
                        pagination: {
                            more: (params.page * 10) < data.total
                        }
                    };
                }
            },
            minimumInputLength: 2,
            templateResult: function(repo) {
                return repo.title;
            },
            templateSelection: function(repo) {
                return repo.title;
            }

        });

        $('#house_reg').select2({
            ajax: {
                url: '/house_new',
                dataType: 'json',
                type: 'GET',
                data: function(params) {
                    $fias_address_object_id = $('#street_reg').val();
                    return {
                        q: params.term,
                        id: $fias_address_object_id,
                        page: params.page
                    };
                },
                processResults: function(data, params) {
                    params.page = params.page || 1;
                    return {
                        results: data,
                        pagination: {
                            more: (params.page * 10) < data.total
                        }
                    };
                }
            },
            templateResult: function(repo) {
                return repo.title;
            },
            templateSelection: function(repo) {
                return repo.title;
            }
        });
        $('#street_fact').on('change', function() {
            $("#house_fact").empty().trigger('change')
        });

         $('#street_fact').select2({
            ajax: {
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                url: '/address_new',
                dataType: 'json',
                delay: 250,
                data: function(params) {
                    return {
                        q: params.term,
                        page: params.page
                    };
                },
                processResults: function(data, params) {
                    params.page = params.page || 1;
                    return {
                        results: data.data,
                        pagination: {
                            more: (params.page * 10) < data.total
                        }
                    };
                }
            },
            minimumInputLength: 2,
            templateResult: function(repo) {
                return repo.title;
            },
            templateSelection: function(repo) {
                return repo.title;
            }
        });
            $('#house_fact').select2({
            ajax: {
                url: '/house_new',
                dataType: 'json',
                type: 'GET',
                data: function(params) {
                    $fias_address_object_id = $('#street_fact').val();
                    return {
                        q: params.term,
                        id: $fias_address_object_id,
                        page: params.page
                    };
                },
                processResults: function(data, params) {
                    params.page = params.page || 1;
                    return {
                        results: data,
                        pagination: {
                            more: (params.page * 10) < data.total
                        }
                    };
                }
            },
            templateResult: function(repo) {
                return repo.title;
            },
            templateSelection: function(repo) {
                return repo.title;
            }
        });
// ИЗМЕНЕНИЕ ТИПА ДОКУМЕНТА
        $('#citizenship').on('change', function() {
            var birth = birthDateToAge(document.querySelector('.birth').value, new Date().getTime());

            if ($('#citizenship').val() == '2') {
                $(".certificate").show();
                $(".passport").hide();
                $(".document1").hide();
                $(".document2").show();
                $(".document2 [name='birth_certificate_number']").parent().find('.error_input').remove();
                    $(".document2 [name='birth_certificate_number']").css("border-color",'#ced4da');

                $('#seria_b_error_input').remove();
                $('[name="birth_certificate_seria_b"]').css("border-color",'#ced4da');

                if(!(birth[0]>=14 && birth[1]>=3)){
                    $(".document1 [name='birth_certificate_seria_a'], [name='birth_certificate_seria_b']").removeAttr(
                        "required maxlength pattern minlength");
                    $(".document2 [name='birth_certificate_number'],[name='birth_certificate_created_at']").attr(
                        "required", 'true');
                    $(".document2 [name='birth_certificate_number']").removeAttr("maxlength minlength");
                }
            }

            else if ($('#citizenship').val() == '3') {
                $(".passport_seria").show();
                $(".passport_snils").show();
                $(".certificate").hide();
                $(".passport").show();
                $("[name='doc_seria']").prop("maxlength", "4");
                $("[name='doc_seria']").attr("minlength", "4");
                $("[name='doc_seria']").attr("required",true);

                $("[name='doc_number']").prop("maxlength", "6");
                $("[name='doc_number']").attr("minlength", "6");
                $('#seria_b_error_input').remove();
                if( !isNaN($('[name="doc_number"]').val()) && !($('[name="doc_number"]').val().length > 6)){
                    $('[name="doc_number"]').parent().find('.error_input').remove();
                    $('[name="doc_number"]').css("border-color",'#ced4da');
                    }else {
                        if( !($('#citizenship').val() == 4) ){
                            $('[name="doc_number"]').css("border-color","red");
                            if ( !$('[name="doc_number"]').parent().find('.error_input').length){
                                $('[name="doc_number"]').parent().append('<div class="text-danger error_input">вводите пожалуйста только 6 чисел </div>');
                            }
                        }
                }
                if( !isNaN($('[name="doc_seria"]').val()) && !($('[name="doc_seria"]').val().length > 4)){
                    $('[name="doc_seria"]').parent().find('.error_input').remove();
                    $('[name="doc_seria"]').css("border-color",'#ced4da');
                    }else {
                        if( !($('#citizenship').val() == 4) ){
                            $('[name="doc_seria"]').css("border-color","red");
                            if ( !$('[name="doc_seria"]').parent().find('.error_input').length){
                                $('[name="doc_seria"]').parent().append('<div class="text-danger error_input">вводите пожалуйста только 4 чисел </div>');
                            }
                        }
                }
            }
            else if ($('#citizenship').val() == '4') {
                $(".passport_seria").hide();
                $(".passport_snils").hide();
                $(".certificate").hide();
                $(".passport").show();
                $(".passport_seria [name='doc_seria']").removeAttr("required");
                $(".passport_seria [name='doc_seria']").removeAttr("minlength");

                $("[name='doc_number']").css("border-color",'#ced4da');
                $("[name='doc_number']").parent().find('.error_input').remove();
                $("[name='doc_seria']").parent().find('.error_input').remove();
                $("[name='doc_seria']").css("border-color",'#ced4da');
                $("[name='doc_number']").removeAttr("minlength").removeAttr("maxlength");
                $('#seria_b_error_input').remove();

            }
            else if($('#citizenship').val() == '1') {
                $(".certificate").show();
                $(".passport").hide();

                $(".document2 [name='birth_certificate_number']").attr("maxlength", "6");
                    $(".document2 [name='birth_certificate_number']").attr("minlength", '6');
                    $(".document1 [name='birth_certificate_seria_b']").attr("minlength", "2");
                    $(".document1 [name='birth_certificate_seria_b']").attr("maxlength", "2");

                if(!(birth[0]>=14 && birth[1]>=3)){
                    $(".document1 [name='birth_certificate_seria_a'], [name='birth_certificate_seria_b']")
                        .attr("required", 'true');

                    $(".document2 [name='birth_certificate_number'],[name='birth_certificate_created_at']")
                        .attr("required", 'true');

                    if((/([a-zA-Z]+)/.test($("#birth_certificate_seria_b").val()))){
                        if ( !$('#birth_certificate_seria_a_label').find('.error_input').length){
                            $("#birth_certificate_seria_b").css("border-color","red");
                            $('#birth_certificate_seria_a_label').append('<span  id = "seria_b_error_input" class="text-danger error_input">вводите пожалуйста кириллицу</span>');
                        }
                    }else {
                        $('#birth_certificate_seria_a_label').find('.error_input').remove();
                        $("#birth_certificate_seria_b").css("border-color",'#ced4da');
                    }

                    if( !isNaN($('#birth_certificate_number').val()) && !($('#birth_certificate_number').val().length > 6)){
                        $('#birth_certificate_number').parent().find('.error_input').remove();
                        $('#birth_certificate_number').css("border-color",'#ced4da');
                    }else {
                        if( !($('#citizenship').val() == "2")){
                            $('#birth_certificate_number').css("border-color","red");
                            if ( !$('#birth_certificate_number').parent().find('.error_input').length){
                                $('#birth_certificate_number').parent().append('<div class="text-danger error_input">вводите пожалуйста только 6 чисел </div>');
                            }
                        }
                    }
                }
                $(".document1").show();
                $(".document2").show();
            }
        });

        $('#child_category').on('change', function() {
            if ($("#child_category").val() >= 1) {
                $(".benefits").removeClass('d-none');
                $("[name='child_categories_scan']").prop('required',true);
            } else {
                $(".benefits").addClass('d-none');
                $("[name='child_categories_scan']").prop('required',false);
            };
        });

        function declOfNum(number, titles) {
            cases = [2, 0, 1, 1, 1, 2];
            return number + " " + titles[(number % 100 > 4 && number % 100 < 20) ? 2 : cases[(number % 10 < 5) ? number % 10 : 5]];
        }

        function birthDateToAge(b, n) {
            var x = new Date(n),
                z = new Date(b),
                b = new Date(b),
                n = new Date(n);
            x.setFullYear(n.getFullYear() - b.getFullYear(), n.getMonth() - b.getMonth(), n.getDate() - b.getDate());
            z.setFullYear(b.getFullYear() + x.getFullYear(), b.getMonth() + x.getMonth() + 1);
            if (z.getTime() == n.getTime()) {
                if (x.getMonth() == 11) {
                    return [x.getFullYear() + 1, 0, 0];
                } else {
                    return [x.getFullYear(), x.getMonth() + 1, 0];
                }
            } else {
                return [x.getFullYear(), x.getMonth(), x.getDate()];
            }
        }
        if ($('#child-data-form .birth').val() != ''){
            let birth = birthDateToAge(document.querySelector('.birth').value, new Date().getTime());
            var hasPassword = "";
            if((birth[0]>=14 && birth[1]>=3) || birth[0]>14){
                console.log(birth);
                $('#child_option_3').prop('disabled',false);
                $('#child_option_3').prop('hidden',false);

                $('#child_option_4').prop('disabled',false);
                $('#child_option_4').prop('hidden',false);


                $('.certificate').hide();
                $('.passport').show();
            }
            else{
                $('#child_option_1').prop('disabled',false);
                $('#child_option_1').prop('hidden',false);

                $('#child_option_2').prop('disabled',false);
                $('#child_option_2').prop('hidden',false);

                $('.certificate').show();
                $('.passport').hide();
            }
            $('#child-data-form').find('.age').html(declOfNum(birth[0], ['год', 'года', 'лет']) + " " + declOfNum(birth[1], ['месяц', 'месяца', 'месяцев']) + " " + declOfNum(birth[2], ['день', 'дня', 'дней']) );
        }

        function appendSpan(elem) {
            if (elem.find('span.text-danger').length == 1) {
                return 0;
            }
            let span = $('<span class="text-danger">*</span>');
            elem.append(span);
        }

        $('#m_name_check').change(function(){
            if($('#m_name_check').prop('checked')){
                $("#m_name").removeAttr('required'); //если чекед то необязательное делать
                $('#m_name_required').find('span.text-danger').remove();
                $("#m_name").attr('readonly', true);
                $("#m_name").val('');
            }
            else{
                $("#m_name").attr('required', true); //обязательное
                $("#m_name").removeAttr('readonly');
                appendSpan($('#m_name_required'));
            }
        });

        $('#doc_number ,#doc_seria, #snils, #birth_certificate_number ').on('input',function(){
            if(this.id == 'snils'){
                var value = $(this).cleanVal();
            }else{
                var value = $(this).val();
            }

            if(!isNaN(value)){
                $(this).parent().find('.error_input').remove();
                $(this).css("border-color",'#ced4da');
            }else {
                if( $('#citizenship').val() != 4 && $('#citizenship').val() != 2 ){
                    $(this).css("border-color","red");
                    if ( !$(this).parent().find('.error_input').length){
                        $(this).parent().append('<div class="text-danger error_input">вводите пожалуйста только числа</div>');
                    }
                }
            }
        });

        $('#l_name , #f_name, #m_name').on('input',function(){
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

        $('#birth_certificate_seria_b').on('input',function(){
            var value = $(this).val();
            if((/([a-zA-Z]+)/.test(value))){
                $(this).css("border-color","red");
                if ( !$('#birth_certificate_seria_a_label').find('.error_input').length){
                    $('#birth_certificate_seria_a_label').append('<span  id = "seria_b_error_input" class="text-danger error_input">вводите пожалуйста кириллицу</span>');
                }
            }else {
                $('#birth_certificate_seria_a_label').find('.error_input').remove();
                $(this).css("border-color",'#ced4da');
            }
        });

        // ИЗМЕНЕНИЕ ДАТЫ
        $('#child-data-form').on('input', '.birth', function(event) {
            event.preventDefault();
            var birth = birthDateToAge(document.querySelector('.birth').value, new Date().getTime());
            let hasPassword = "";
            if((birth[0]>=14 && birth[1]>=3) || birth[0]>14){
                $('#seria_b_error_input').remove();
                $('.passport #doc_created_at').prop('required',true);
                $('.passport #doc_seria').prop('required',true);
                $('.passport #doc_number').prop('required',true).prop('readonly',false);

                $('.certificate #birth_certificate_seria_a').prop('required',false);
                $('.certificate #birth_certificate_seria_b').prop('required',false);
                $('.certificate #birth_certificate_number').prop('required',false);
                $('.certificate #birth_certificate_created_at').prop('required',false);

                $('.certificate #birth_certificate_seria_a_label').find('span.text-danger').remove();
                $('.certificate #birth_certificate_number_label').find('span.text-danger').remove();
                $('.certificate #birth_certificate_created_at_label').find('span.text-danger').remove();


                appendSpan( $('.passport #doc_number_label'));
                appendSpan( $('.passport #doc_seria_label'));
                appendSpan( $('.passport #doc_created_at_label'));

                //если больше 14 лет то скрываю option свидетельств
                $('#child_option_2').prop('disabled',true).prop('hidden',true);
                $('#child_option_1').prop('disabled',true).prop('hidden',true);

                //console.log($('#citizenship').val());
                //если больше 14 лет то открывю паспорт (только)
                if($('#citizenship').val() == 4){
                    $('#child_option_4').prop('hidden',false).prop('disabled',false).prop('selected', true)
                }else{
                    $('#child_option_3').prop('hidden',false).prop('disabled',false).prop('selected', true)
                    $('#child_option_4').prop('hidden',false).prop('disabled',false);
                }
                $('.certificate').hide();
                $('.passport').show();
            }else{

                $('.passport #doc_created_at').prop('required',false);
                $('.passport #doc_seria').prop('required',false);
                $('.passport #doc_number').prop('required',false);


                if( !($('#citizenship').val() == '2')){
                    $('.certificate #birth_certificate_seria_a').prop('required',true);
                    $('.certificate #birth_certificate_seria_b').prop('required',true);
                }

                $('.certificate #birth_certificate_number').prop('required',true);
                $('.certificate #birth_certificate_created_at').prop('required',true);

                //appendSpan( $('#birth_certificate_seria_a_label'));
                appendSpan( $('#birth_certificate_number_label'));
                appendSpan( $('#birth_certificate_created_at_label'));

                $('.passport #doc_created_at_label').find('span.text-danger').remove();
                $('.passport #doc_seria_label').find('span.text-danger').remove();
                $('.passport #doc_number_label').find('span.text-danger').remove();

                //если меньше 14 лет то открывю option свидетельств
                if($('#citizenship').val() == 2){
                    $('#child_option_2').prop('disabled',false).prop('hidden',false).prop('selected', true);
                    $('#child_option_1').prop('disabled',false).prop('hidden',false);
                }else{
                    $('#child_option_2').prop('disabled',false).prop('hidden',false);
                    $('#child_option_1').prop('disabled',false).prop('hidden',false).prop('selected', true);
                }
                //если меньше 14 лет то скрываю паспорт
                $('#child_option_3').prop('hidden',true).prop('disabled',true);
                $('#child_option_4').prop('hidden',true).prop('disabled',true);
                $('.certificate').show();
                $('.passport').hide();
            }
            error_checking();
            $('#child-data-form').find('.age').html(declOfNum(birth[0], ['год', 'года', 'лет']) + " " + declOfNum(birth[1], ['месяц', 'месяца', 'месяцев']) + " " + declOfNum(birth[2], ['день', 'дня', 'дней']));
        });


        $('#address_equal').change(function(){
            // console.log($(this))
            if($('#address_equal').prop('checked')){
                $('.address_fact').hide();
            }
            else{
                $('.address_fact').show();
            }
        });

        $('#UserAddHouse').on('click', function(event) {
            event.preventDefault();
            $('#modalAjax').load("/layouts/UserAddHouse");
        });

        function error_checking () {
            console.log("типа документа: " + $('#citizenship').val());
            switch ($('#citizenship').val()) {
            case '1':
                //для birth_certificate_seria_b - убираю ошибку
            if((/([a-zA-Z]+)/.test($('#birth_certificate_seria_b').val()))){
                $('#birth_certificate_seria_b').css("border-color","red");
                if ( !$('#birth_certificate_seria_a_label').find('.error_input').length){
                    $('#birth_certificate_seria_a_label').append('<span  id = "seria_b_error_input" class="text-danger error_input">вводите пожалуйста кириллицу</span>');
                }
            }else{
                $('#seria_b_error_input').remove();
                $('#birth_certificate_seria_b').css("border-color",'#ced4da');
            }
                //для номера свидеетельтва (birth_certificate_number)- убираю ошибку
                if( !isNaN($('#birth_certificate_number').val()) && !($('#birth_certificate_number').val().length > 6)){
                        $('#birth_certificate_number').parent().find('.error_input').remove();
                        $('#birth_certificate_number').css("border-color",'#ced4da');
                    }else {
                        if( !($('#citizenship').val() == "2")){
                            $('#birth_certificate_number').css("border-color","red");
                            if ( !$('#birth_certificate_number').parent().find('.error_input').length){
                                $('#birth_certificate_number').parent().append('<div class="text-danger error_input">вводите пожалуйста только 6 чисел </div>');
                            }
                        }
                    }

                $("#doc_seria").parent().find('.error_input').remove();
                $("#doc_seria").css("border-color",'#ced4da');
                $("#doc_number").parent().find('.error_input').remove();
                $("#doc_number").css("border-color",'#ced4da');
                break;

            case '2':
            console.log("22222");
                //для birth_certificate_seria_b - убираю ошибку
                    $('#seria_b_error_input').remove();
                    $('#birth_certificate_seria_b').css("border-color",'#ced4da');

                //для номера свидеетельтва (birth_certificate_number)- убираю ошибку
                $('#birth_certificate_number').parent().find('.error_input').remove();
                $('#birth_certificate_number').css("border-color",'#ced4da');

                $(".document2 [name='birth_certificate_number']").parent().find('.error_input').remove();
                $(".document2 [name='birth_certificate_number']").css("border-color",'#ced4da');
                $('#seria_b_error_input').remove();
                $("#doc_seria").parent().find('.error_input').remove();
                $("#doc_seria").css("border-color",'#ced4da');
                $("#doc_number").parent().find('.error_input').remove();
                $("#doc_number").css("border-color",'#ced4da');
                break;
            case '3':
            console.log("3333333");
                //для birth_certificate_seria_b - убираю ошибку
                    $('#seria_b_error_input').remove();
                    $('#birth_certificate_seria_b').css("border-color",'#ced4da');

                //для номера свидеетельтва (birth_certificate_number)- убираю ошибку
                $('#birth_certificate_number').parent().find('.error_input').remove();
                $('#birth_certificate_number').css("border-color",'#ced4da');


                if(!isNaN($("#doc_seria").val())){
                    $("#doc_seria").parent().find('.error_input').remove();
                    $("#doc_seria").css("border-color",'#ced4da');
                }else {
                    if( $('#citizenship').val() != 4 && $('#citizenship').val() != 2 ){
                        $("#doc_seria").css("border-color","red");
                        if ( !$("#doc_seria").parent().find('.error_input').length){
                            $("#doc_seria").parent().append('<div class="text-danger error_input">вводите пожалуйста только числа</div>');
                        }
                    }
                }

                $('#seria_b_error_input').remove();
                if( !isNaN($('[name="doc_number"]').val()) && !($('[name="doc_number"]').val().length > 6)){
                    $('[name="doc_number"]').parent().find('.error_input').remove();
                    $('[name="doc_number"]').css("border-color",'#ced4da');
                    }else {
                        if( !($('#citizenship').val() == 4) ){
                            $('[name="doc_number"]').css("border-color","red");
                            if ( !$('[name="doc_number"]').parent().find('.error_input').length){
                                $('[name="doc_number"]').parent().append('<div class="text-danger error_input">вводите пожалуйста только 6 чисел </div>');
                            }
                        }
                }

                break;
            case '4':
            console.log("4444");
                //для birth_certificate_seria_b - убираю ошибку
                    $('#seria_b_error_input').remove();
                    $('#birth_certificate_seria_b').css("border-color",'#ced4da');
                //для номера свидеетельтва (birth_certificate_number)- убираю ошибку
                $('#birth_certificate_number').parent().find('.error_input').remove();
                $('#birth_certificate_number').css("border-color",'#ced4da');

                $("[name='doc_number']").parent().find('.error_input').remove();
                $('#seria_b_error_input').remove();
                break;
            }
        }
    })()
</script>
