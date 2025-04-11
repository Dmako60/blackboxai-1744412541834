import 'package:flutter/material.dart';
import 'custom_button.dart';

class CustomDialog extends StatelessWidget {
  final String title;
  final String message;
  final String? confirmText;
  final String? cancelText;
  final VoidCallback? onConfirm;
  final VoidCallback? onCancel;
  final bool isDestructive;
  final Widget? content;

  const CustomDialog({
    Key? key,
    required this.title,
    required this.message,
    this.confirmText,
    this.cancelText,
    this.onConfirm,
    this.onCancel,
    this.isDestructive = false,
    this.content,
  }) : super(key: key);

  @override
  Widget build(BuildContext context) {
    return AlertDialog(
      shape: RoundedRectangleBorder(
        borderRadius: BorderRadius.circular(16),
      ),
      title: Text(
        title,
        style: Theme.of(context).textTheme.titleLarge?.copyWith(
              fontWeight: FontWeight.bold,
            ),
        textAlign: TextAlign.center,
      ),
      content: Column(
        mainAxisSize: MainAxisSize.min,
        children: [
          if (content != null) ...[
            content!,
            SizedBox(height: 16),
          ],
          Text(
            message,
            style: Theme.of(context).textTheme.bodyMedium,
            textAlign: TextAlign.center,
          ),
        ],
      ),
      actions: [
        if (cancelText != null)
          CustomButton(
            text: cancelText!,
            onPressed: () {
              Navigator.of(context).pop();
              if (onCancel != null) onCancel!();
            },
            type: CustomButtonType.outline,
          ),
        if (confirmText != null)
          CustomButton(
            text: confirmText!,
            onPressed: () {
              Navigator.of(context).pop();
              if (onConfirm != null) onConfirm!();
            },
            type: isDestructive ? CustomButtonType.secondary : CustomButtonType.primary,
          ),
      ],
      actionsAlignment: MainAxisAlignment.spaceEvenly,
      actionsPadding: EdgeInsets.symmetric(horizontal: 16, vertical: 8),
    );
  }
}

// Helper class for showing different types of dialogs
class DialogHelper {
  static Future<void> showError(
    BuildContext context, {
    String title = 'Error',
    required String message,
    String buttonText = 'OK',
  }) {
    return showDialog(
      context: context,
      builder: (context) => CustomDialog(
        title: title,
        message: message,
        confirmText: buttonText,
      ),
    );
  }

  static Future<void> showSuccess(
    BuildContext context, {
    String title = 'Success',
    required String message,
    String buttonText = 'OK',
  }) {
    return showDialog(
      context: context,
      builder: (context) => CustomDialog(
        title: title,
        message: message,
        confirmText: buttonText,
      ),
    );
  }

  static Future<bool> showConfirmation(
    BuildContext context, {
    required String title,
    required String message,
    String confirmText = 'Confirm',
    String cancelText = 'Cancel',
    bool isDestructive = false,
  }) async {
    final result = await showDialog<bool>(
      context: context,
      builder: (context) => CustomDialog(
        title: title,
        message: message,
        confirmText: confirmText,
        cancelText: cancelText,
        isDestructive: isDestructive,
        onConfirm: () => Navigator.of(context).pop(true),
        onCancel: () => Navigator.of(context).pop(false),
      ),
    );
    return result ?? false;
  }

  static Future<void> showLoading(
    BuildContext context, {
    String message = 'Please wait...',
  }) {
    return showDialog(
      context: context,
      barrierDismissible: false,
      builder: (context) => WillPopScope(
        onWillPop: () async => false,
        child: AlertDialog(
          shape: RoundedRectangleBorder(
            borderRadius: BorderRadius.circular(16),
          ),
          content: Column(
            mainAxisSize: MainAxisSize.min,
            children: [
              CircularProgressIndicator(),
              SizedBox(height: 16),
              Text(message),
            ],
          ),
        ),
      ),
    );
  }
}
