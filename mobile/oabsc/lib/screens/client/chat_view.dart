import 'package:flutter/material.dart';
import 'package:webview_flutter/webview_flutter.dart';
import '../../theme/app_theme.dart';
import '../../services/auth_service.dart';

class ChatView extends StatefulWidget {
  final VoidCallback onBack;

  const ChatView({super.key, required this.onBack});

  @override
  State<ChatView> createState() => _ChatViewState();
}

class _ChatViewState extends State<ChatView> {
  final AuthService _authService = AuthService();
  String _userName = 'Guest';
  String _userEmail = '';
  bool _isLoading = true;
  WebViewController? _controller;

  @override
  void initState() {
    super.initState();
    _loadUserData();
  }

  Future<void> _loadUserData() async {
    final name = await _authService.getSavedName();
    
    _userName = name ?? 'Client';

    final controller = WebViewController()
      ..setJavaScriptMode(JavaScriptMode.unrestricted)
      ..setNavigationDelegate(
        NavigationDelegate(
          onPageFinished: (String url) {
            setState(() {
              _isLoading = false;
            });
            // Inject visitor info
            _controller?.runJavaScript('''
              var Tawk_API = Tawk_API || {};
              Tawk_API.onLoad = function() {
                Tawk_API.setAttributes({
                  'name': '$_userName',
                  'email': '${_userEmail.isEmpty ? "client@oabsc.com" : _userEmail}'
                }, function(error) {});
              };
            ''');
          },
        ),
      )
      ..loadRequest(Uri.parse('https://tawk.to/chat/69f6ccc10f7c9c1c2fb08239/1jnm0vl6a'));

    setState(() {
      _controller = controller;
    });
  }

  @override
  Widget build(BuildContext context) {
    return Column(
      children: [
        // Chat Widget
        Expanded(
          child: Stack(
            children: [
              if (_controller != null)
                WebViewWidget(controller: _controller!),
              if (_isLoading || _controller == null)
                const Center(child: CircularProgressIndicator()),
            ],
          ),
        ),
      ],
    );
  }
}
