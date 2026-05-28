@php
    use App\Models\MessageThread;
    $isOnMessages = request()->is('principal/messages*');
    if ($isOnMessages) return;
    $unread = (int) MessageThread::sum('unread_count');
@endphp

<a href="{{ url('/principal/messages') }}"
   title="Open Messages"
   style="position: fixed; right: 1.5rem; bottom: 1.5rem; z-index: 60;
          width: 3.4rem; height: 3.4rem; border-radius: 999px;
          background: linear-gradient(135deg, #6366f1, #4f46e5);
          color: #fff; display: inline-flex; align-items: center; justify-content: center;
          box-shadow: 0 10px 25px -5px rgba(79,70,229,.45), 0 4px 10px -3px rgba(0,0,0,.18);
          transition: transform .15s ease;">
    <span style="position: relative; display: inline-flex; align-items: center; justify-content: center;">
        <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M8 12h.01M12 12h.01M16 12h.01M21 12a9 9 0 1 1-3.85-7.4L21 4l-1.1 3.85A8.96 8.96 0 0 1 21 12z"/>
        </svg>
        @if($unread > 0)
            <span style="position: absolute; top: -.4rem; right: -.55rem;
                         min-width: 1.1rem; height: 1.1rem; padding: 0 .3rem;
                         background: #f59e0b; color: #fff; font-size: .65rem; font-weight: 700;
                         border-radius: 999px; display: inline-flex; align-items: center; justify-content: center;
                         border: 2px solid #fff; line-height: 1;">{{ $unread > 99 ? '99+' : $unread }}</span>
        @endif
    </span>
</a>
