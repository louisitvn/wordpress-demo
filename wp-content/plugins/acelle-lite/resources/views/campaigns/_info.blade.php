            <h3 class="mt-10"><span class="text-teal text-bold">{{ $campaign->subscribers()->count() }}</span> {{ trans('messages.' . \Acelle\Library\Tool::getPluralPrase('recipient', $campaign->subscribers()->count())) }}</h3>
            
            <div class="row">
                <div class="col-md-6 campaigns-summary">
                    <div class="mb-10">
                        <span class="text-bold text-muted">{{ trans('messages.from') }}:</span>
                        @if (is_object($campaign->segment))
                            {{ $campaign->mailList->name }} . {{ $campaign->segment->name }}                        
                        @elseif (is_object($campaign->mailList))
                            {{ $campaign->mailList->name }}
                        @endif
                    </div>
                    <div class="mb-10">
                        <span class="text-bold text-muted">{{ trans('messages.subject') }}:</span>
                        {{ $campaign->subject }}  
                    </div>
                    <div class="mb-10">
                        <span class="text-bold text-muted">{{ trans('messages.from_email') }}:</span>
                        {{ $campaign->from_email }}  
                    </div>
                    <div class="mb-10">
                        <span class="text-bold text-muted">{{ trans('messages.from_name') }}:</span>
                        {{ $campaign->from_name }}  
                    </div>
                    
                </div>
                <div class="col-md-6">
                    <div class="mb-10">
                        <span class="text-bold text-muted">{{ trans('messages.reply_to') }}:</span>
                        {{ $campaign->reply_to }}  
                    </div>
                    <div class="mb-10">
                        <span class="text-bold text-muted">{{ trans('messages.updated_at') }}:</span>
                        {{ Tool::formatDateTime($campaign->updated_at) }}
                    </div>
                    <div class="mb-10">
                        <span class="text-bold text-muted">{{ trans('messages.run_at') }}:</span>
                        {{ isset($campaign->run_at) ? Tool::formatDateTime($campaign->run_at) : "" }}
                    </div>
                    <div class="mb-10">
                        <span class="text-bold text-muted">{{ trans('messages.delivery_at') }}:</span>
                        {{ isset($campaign->delivery_at) ? Tool::formatDateTime(\Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $campaign->delivery_at)) : "" }}
                    </div>
                </div>
            </div>