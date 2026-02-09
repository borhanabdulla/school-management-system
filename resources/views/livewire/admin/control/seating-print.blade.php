<div>
    <div class="no-print">
        <button onclick="window.print()">🖨️ طباعة</button>
        <button onclick="window.history.back()">عودة</button>
    </div>

    @if($printType === 'seating' || $printType === 'both')
        <div class="header">
            <h1>كشف أرقام الجلوس</h1>
            <p>{{ $this->session->name ?? '' }} | {{ $this->session->academicYear->name ?? '' }} - {{ $this->session->term->name ?? '' }}</p>
        </div>

        <table>
            <thead>
                <tr>
                    <th style="width: 50px">#</th>
                    <th>اسم الطالب</th>
                    <th style="width: 100px">الصف</th>
                    <th style="width: 100px">رقم الجلوس</th>
                    @if($printType === 'both')
                        <th style="width: 100px">الرقم السري</th>
                    @endif
                </tr>
            </thead>
            <tbody>
                @foreach($this->seatings as $index => $seating)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>{{ $seating->student->full_name_ar ?? 'N/A' }}</td>
                        <td>{{ $seating->student->currentClassSection->grade->name ?? '' }} / {{ $seating->student->currentClassSection->name ?? '' }}</td>
                        <td style="font-weight: bold; text-align: center; font-size: 14pt;">{{ $seating->seat_number }}</td>
                        @if($printType === 'both')
                            <td style="font-weight: bold; text-align: center; font-family: monospace;">{{ $seating->secret_number }}</td>
                        @endif
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    @if($printType === 'secret')
        <div class="header">
            <h1>ملصقات الأرقام السرية</h1>
            <p>{{ $this->session->name ?? '' }} | للاستخدام الداخلي فقط - سري</p>
        </div>

        <div class="sticker-grid">
            @foreach($this->seatings as $seating)
                <div class="sticker">
                    <div class="secret">{{ $seating->secret_number }}</div>
                    <div class="seat">جلوس: {{ $seating->seat_number }}</div>
                </div>
            @endforeach
        </div>
    @endif

    <div class="no-print" style="margin-top: 30px; font-size: 10pt; color: #999;">
        تم إنشاء هذا الكشف في {{ now()->format('Y-m-d H:i') }}
    </div>
</div>
